<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Commands;

use Illuminate\Console\Command;
use Moneo\LaravelRag\Evals\RagEval;
use Moneo\LaravelRag\Exceptions\RagException;
use Moneo\LaravelRag\Facades\Rag;

class RagEvalCommand extends Command
{
    protected $signature = 'rag:eval
        {--suite= : Path to JSON eval suite file}
        {--model= : The Eloquent model class}
        {--fail-below=0.8 : Minimum score threshold to pass}
        {--json : Output results as JSON}';

    protected $description = 'Run RAG evaluation suite';

    public function handle(): int
    {
        $suitePath = $this->option('suite');
        $modelClass = $this->option('model') ?? 'App\\Models\\Document';
        $failBelow = (float) $this->option('fail-below');
        $jsonOutput = (bool) $this->option('json');

        $pipeline = Rag::from($modelClass);
        $eval = RagEval::suite()->using($pipeline);

        if ($suitePath) {
            if (! file_exists($suitePath)) {
                $this->error("Suite file not found: {$suitePath}");

                return self::FAILURE;
            }
            $eval->loadFromFile($suitePath);
        } else {
            $this->error('Please provide a suite file via --suite');

            return self::FAILURE;
        }

        try {
            $this->info('Running evaluation suite...');
            $report = $eval->run();

            if ($jsonOutput) {
                $this->line($report->toJson());

                return $report->passes($failBelow) ? self::SUCCESS : self::FAILURE;
            }

            // Display table
            $table = $report->toTable();
            $this->table($table['headers'], $table['rows']);

            $this->newLine();
            $this->info(sprintf('Total time: %.0fms', $report->totalTimeMs));

            if ($report->passes($failBelow)) {
                $this->info("PASS: All metrics above {$failBelow}");

                return self::SUCCESS;
            }

            $this->error("FAIL: Some metrics below {$failBelow}");

            return self::FAILURE;
        } catch (RagException $e) {
            $this->error("RAG error: {$e->getMessage()}");

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error("Unexpected error: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
