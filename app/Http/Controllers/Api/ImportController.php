<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    /**
     * Import SQL data dari raw SQL file
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importSql(Request $request)
    {
        try {
            // Validasi: pastikan ada content dalam request
            $sql = $request->getContent();
            
            if (empty($sql)) {
                return response()->json([
                    'success' => false,
                    'message' => 'SQL content tidak boleh kosong'
                ], 400);
            }

            // Split SQL statements by semicolon (simple parsing)
            // Remove comments dan whitespace
            $sql = $this->cleanSql($sql);
            
            // Split by semicolon untuk multiple statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                fn($stmt) => !empty($stmt)
            );

            if (empty($statements)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada SQL statement yang valid'
                ], 400);
            }

            Log::info('Import SQL dimulai', [
                'total_statements' => count($statements),
                'user_ip' => $request->ip()
            ]);

            $executedCount = 0;
            $errors = [];

            // Execute setiap statement
            foreach ($statements as $index => $statement) {
                try {
                    DB::statement($statement);
                    $executedCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'statement_index' => $index,
                        'error' => $e->getMessage(),
                        'statement' => substr($statement, 0, 100) . '...'
                    ];
                    
                    Log::warning('SQL Statement Error', [
                        'index' => $index,
                        'error' => $e->getMessage(),
                        'statement' => substr($statement, 0, 200)
                    ]);
                }
            }

            Log::info('Import SQL selesai', [
                'executed' => $executedCount,
                'total' => count($statements),
                'errors' => count($errors)
            ]);

            return response()->json([
                'success' => count($errors) == 0,
                'message' => "Import selesai. {$executedCount} dari " . count($statements) . " statement berhasil dijalankan",
                'executed' => $executedCount,
                'total' => count($statements),
                'errors' => $errors
            ], count($errors) == 0 ? 200 : 206);

        } catch (\Exception $e) {
            Log::error('Import SQL Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimport data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bersihkan SQL dari komentar dan whitespace
     * 
     * @param string $sql
     * @return string
     */
    private function cleanSql(string $sql): string
    {
        // Hapus SQL comments (-- dan /* */)
        $sql = preg_replace('/--.*$/m', '', $sql); // Hapus -- comments
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Hapus /* */ comments
        
        // Hapus multiple spaces & newlines
        $sql = preg_replace('/\s+/', ' ', $sql);
        
        return trim($sql);
    }
}
