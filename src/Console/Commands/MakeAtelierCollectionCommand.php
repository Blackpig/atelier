<?php

namespace BlackpigCreatif\Atelier\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeAtelierCollectionCommand extends Command
{
    protected $signature = 'make:atelier-collection {name? : The name of the block collection}';

    protected $description = 'Create a new Atelier block collection class';

    public function handle(): int
    {
        $name = $this->argument('name') ?? $this->ask('What is the collection name?');

        if (! $name) {
            $this->error('Collection name is required');

            return self::FAILURE;
        }

        // Ensure name ends with 'Blocks' or 'Collection'
        if (! Str::endsWith($name, ['Blocks', 'Collection'])) {
            $name .= 'Blocks';
        }

        // Ensure it's StudlyCase
        $name = Str::studly($name);

        // Generate class
        $namespace = 'App\\BlackpigCreatif\\Atelier\\Collections';
        $path = app_path('BlackpigCreatif/Atelier/Collections');

        // Create directory if it doesn't exist
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $filePath = $path.'/'.$name.'.php';

        // Check if file already exists
        if (file_exists($filePath)) {
            $this->error("Collection {$name} already exists!");

            return self::FAILURE;
        }

        // Generate the class content
        $stub = $this->getStub();
        $stub = str_replace('{{ namespace }}', $namespace, $stub);
        $stub = str_replace('{{ class }}', $name, $stub);
        $stub = str_replace('{{ label }}', Str::headline($name), $stub);

        // Write file
        file_put_contents($filePath, $stub);

        $this->info("Block collection [{$name}] created successfully.");
        $this->info("Location: {$filePath}");
        $this->newLine();
        $this->comment('Next steps:');
        $this->comment('1. Add block classes to the getBlocks() method');
        $this->comment('2. Use the collection: BlockManager::make(\'blocks\')->blocks('.$name.'::class)');

        return self::SUCCESS;
    }

    protected function getStub(): string
    {
        return <<<'PHP'
<?php

namespace {{ namespace }};

use BlackpigCreatif\Atelier\Abstracts\BaseBlockCollection;
// Import your block classes here
// use BlackpigCreatif\Atelier\Blocks\HeroBlock;
// use App\BlackpigCreatif\Atelier\Blocks\CustomBlock;

class {{ class }} extends BaseBlockCollection
{
    public function getBlocks(): array
    {
        return [
            // Add your block classes here
            // HeroBlock::class,
            // CustomBlock::class,
        ];
    }

    public static function getLabel(): string
    {
        return '{{ label }}';
    }

    public static function getDescription(): ?string
    {
        return 'Description of your block collection.';
    }
}

PHP;
    }
}
