<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Moneo\LaravelRag\Facades\Rag;

class RagTestPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'RAG';

    protected static ?string $navigationLabel = 'RAG Tester';

    protected static ?string $title = 'RAG Test Console';

    protected static string $view = 'rag::filament.pages.rag-test';

    public string $model = '';

    public string $question = '';

    public int $limit = 5;

    public bool $useHybrid = false;

    public bool $useRerank = false;

    public ?string $answer = null;

    public array $sources = [];

    public ?array $timing = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('model')
                    ->label('Model Class')
                    ->placeholder('App\\Models\\Document')
                    ->required(),
                Textarea::make('question')
                    ->label('Question')
                    ->required()
                    ->rows(3),
                TextInput::make('limit')
                    ->numeric()
                    ->default(5)
                    ->minValue(1)
                    ->maxValue(50),
                Toggle::make('useHybrid')
                    ->label('Hybrid Search'),
                Toggle::make('useRerank')
                    ->label('Re-ranking'),
            ]);
    }

    public function submit(): void
    {
        $pipeline = Rag::from($this->model)->limit($this->limit);

        if ($this->useHybrid) {
            $pipeline = $pipeline->hybrid();
        }

        if ($this->useRerank) {
            $pipeline = $pipeline->rerank();
        }

        $result = $pipeline->askWithSources($this->question);

        $this->answer = $result->answer;
        $this->sources = $result->sources()->toArray();
        $this->timing = [
            'retrieval_ms' => round($result->retrievalTimeMs, 2),
            'generation_ms' => round($result->generationTimeMs, 2),
            'total_ms' => round($result->totalTimeMs(), 2),
        ];
    }
}
