<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;

class DumpPortalHtml extends Command
{
    protected $signature = 'debug:dump-portal-html {token}';
    protected $description = 'Dump the rendered HTML for portal page';

    public function handle()
    {
        $token = $this->argument('token');
        
        $request = DocumentRequest::where('access_token', $token)
            ->with([
                'supplier', 
                'requestParts.requestDocuments.documentType',
                'requestParts.requestDocuments.histories',
                'requestDocuments.documentType', 
                'requestDocuments.histories'
            ])
            ->first();

        if (!$request) {
            $this->error('Request not found');
            return 1;
        }

        // Calculate total progress from ALL parts
        $totalMandatory = 0;
        $totalSubmitted = 0;
        
        if ($request->hasMultipleParts()) {
            foreach ($request->requestParts as $part) {
                $partMandatory = $part->requestDocuments()
                    ->whereHas('documentType', function ($q) {
                        $q->where('is_mandatory', true);
                    })
                    ->count();
                    
                $partSubmitted = $part->requestDocuments()
                    ->whereHas('documentType', function ($q) {
                        $q->where('is_mandatory', true);
                    })
                    ->whereNotNull('file_path')
                    ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                    ->count();
                    
                $totalMandatory += $partMandatory;
                $totalSubmitted += $partSubmitted;
            }
        } else {
            $totalMandatory = $request->requestDocuments()
                ->whereHas('documentType', function ($q) {
                    $q->where('is_mandatory', true);
                })
                ->count();

            $totalSubmitted = $request->requestDocuments()
                ->whereHas('documentType', function ($q) {
                    $q->where('is_mandatory', true);
                })
                ->whereNotNull('file_path')
                ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                ->count();
        }

        $progress = $totalMandatory > 0 ? (int) (($totalSubmitted / $totalMandatory) * 100) : 0;

        $documentRequests = $request->requestDocuments()
            ->with(['documentType', 'histories'])
            ->get()
            ->groupBy(function ($doc) {
                return $doc->status === 'REVISE' ? 'revise' : 'other';
            });

        try {
            $html = View::make('portal.upload', compact('request', 'documentRequests', 'progress', 'token', 'totalMandatory', 'totalSubmitted'))->render();
            
            $outputFile = storage_path('logs/portal-html-dump.html');
            file_put_contents($outputFile, $html);
            
            $this->info("HTML dumped to: {$outputFile}");
            $this->info("File size: " . filesize($outputFile) . " bytes");
            
            // Count occurrences of part headers
            $part1Count = substr_count($html, 'Part 1:');
            $part2Count = substr_count($html, 'Part 2:');
            
            $this->info("Part 1 headers found: {$part1Count}");
            $this->info("Part 2 headers found: {$part2Count}");
            
            // Check for DEBUG comments
            $debugCount = substr_count($html, '<!-- DEBUG:');
            $this->info("DEBUG comments found: {$debugCount}");
            
        } catch (\Exception $e) {
            $this->error('Error rendering view: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
