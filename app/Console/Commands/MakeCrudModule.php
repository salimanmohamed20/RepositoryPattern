<?php
// app/Console/Commands/MakeCrudModule.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeCrudModule extends Command
{
    protected $signature = 'make:crud-module {name}';
    protected $description = 'Generate Interface, Repository, Service, Controller, Request with bindings';

    public function handle()
    {
        $name = $this->argument('name');

        $this->makeInterface($name);
        $this->makeRepository($name);
        $this->makeService($name);
        $this->makeRequest($name);
        $this->makeController($name);
        $this->bindRepository($name);

        $this->info("CRUD Module for {$name} created successfully ✅");
    }

    protected function makeInterface($name)
    {
        $path = app_path("Repositories/Interfaces/{$name}RepositoryInterface.php");
        File::ensureDirectoryExists(dirname($path));

        File::put($path, <<<PHP
<?php

namespace App\Repositories\Interfaces;

interface {$name}RepositoryInterface
{
    public function all();
    public function find(\$id);
    public function create(array \$data);
    public function update(\$id, array \$data);
    public function delete(\$id);
}
PHP);
    }

    protected function makeRepository($name)
    {
        $path = app_path("Repositories/Eloquent/{$name}Repository.php");
        File::ensureDirectoryExists(dirname($path));

        File::put($path, <<<PHP
<?php

namespace App\Repositories\Eloquent;

use App\Models\\{$name};
use App\Repositories\Interfaces\\{$name}RepositoryInterface;

class {$name}Repository implements {$name}RepositoryInterface
{
    public function all()
    {
        return {$name}::all();
    }

    public function find(\$id)
    {
        return {$name}::findOrFail(\$id);
    }

    public function create(array \$data)
    {
        return {$name}::create(\$data);
    }

    public function update(\$id, array \$data)
    {
        \$item = \$this->find(\$id);
        \$item->update(\$data);
        return \$item;
    }

    public function delete(\$id)
    {
        return {$name}::destroy(\$id);
    }
}
PHP);
    }

    protected function makeService($name)
    {
        $path = app_path("Services/{$name}Service.php");
        File::ensureDirectoryExists(dirname($path));

        File::put($path, <<<PHP
<?php

namespace App\Services;

use App\Repositories\Interfaces\\{$name}RepositoryInterface;

class {$name}Service
{
    public function __construct(
        protected {$name}RepositoryInterface \$repository
    ) {}

    public function all()     { return \$this->repository->all(); }
    public function find(\$id){ return \$this->repository->find(\$id); }
    public function create(array \$data){ return \$this->repository->create(\$data); }
    public function update(\$id, array \$data){ return \$this->repository->update(\$id, \$data); }
    public function delete(\$id){ return \$this->repository->delete(\$id); }
}
PHP);
    }

    protected function makeRequest($name)
    {
        $path = app_path("Http/Requests/{$name}Request.php");

        File::put($path, <<<PHP
<?php

namespace App\Http\Requests;

class {$name}Request extends BaseFormRequest
{
    public function rules(): array
    {
        return [
           
        ];
    }
}
PHP);
    }

    protected function makeController($name)
    {
        $path = app_path("Http/Controllers/Api/{$name}Controller.php");
        File::ensureDirectoryExists(dirname($path));

        File::put($path, <<<PHP
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\\{$name}Request;
use App\Services\\{$name}Service;

class {$name}Controller extends Controller
{
    public function __construct(
        protected {$name}Service \$service
    ) {}

    public function index()
    {
        return \$this->service->all();
    }

    public function store({$name}Request \$request)
    {
        return \$this->service->create(\$request->validated());
    }

    public function show(\$id)
    {
        return \$this->service->find(\$id);
    }

    public function update({$name}Request \$request, \$id)
    {
        return \$this->service->update(\$id, \$request->validated());
    }

    public function destroy(\$id)
    {
        return \$this->service->delete(\$id);
    }
}
PHP);
    }
protected function bindRepository($name)
{
    $providerPath = app_path('Providers/RepositoryServiceProvider.php');

    if (!file_exists($providerPath)) {
        $this->error('RepositoryServiceProvider not found!');
        return;
    }

    $bindingLine = "        \$this->app->bind(
            \\App\\Repositories\\Interfaces\\{$name}RepositoryInterface::class,
            \\App\\Repositories\\Eloquent\\{$name}Repository::class
        );";

    $content = file_get_contents($providerPath);

    if (str_contains($content, "{$name}RepositoryInterface")) {
        $this->warn("Binding for {$name} already exists.");
        return;
    }

    $pattern = '/public function register\(\): void\s*\{\s*/';

    if (preg_match($pattern, $content)) {
        $content = preg_replace(
            $pattern,
            "public function register(): void\n    {\n{$bindingLine}\n\n",
            $content,
            1
        );

        file_put_contents($providerPath, $content);
        $this->info("Repository binding added to RepositoryServiceProvider ✅");
    } else {
        $this->error("Could not find register() method in RepositoryServiceProvider.");
    }
}

}
