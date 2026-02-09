# 🚀 Laravel CRUD Module Generator

A small Artisan helper to generate a complete CRUD module following the Repository pattern, with a Service layer, Form Requests, and automatic binding.

## Features

- Generates a Repository Interface and Eloquent implementation
- Generates a Service class to encapsulate business logic
- Generates an API Controller with standard CRUD methods
- Generates a Form Request that extends a shared `BaseFormRequest`
- Adds automatic binding in `RepositoryServiceProvider`

## Architecture (example)

Generated layout (example):

```
app/
├── Repositories/
│   ├── Interfaces/
│   │   └── UserRepositoryInterface.php
│   └── Eloquent/
│       └── UserRepository.php
├── Services/
│   └── UserService.php
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── UserController.php
│   └── Requests/
│       ├── BaseFormRequest.php
│       └── UserRequest.php
└── Providers/
    └── RepositoryServiceProvider.php
```

## Quick Setup

1. Create a shared `BaseFormRequest` (one-time):

```php
// app/Http/Requests/BaseFormRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    abstract public function rules(): array;
}
```

2. Ensure you have a `RepositoryServiceProvider` (create if needed):

```bash
php artisan make:provider RepositoryServiceProvider
```

3. Add the generator command (example name `MakeCrudModule`):

```bash
php artisan make:command MakeCrudModule
# Put the implementation in app/Console/Commands/MakeCrudModule.php
```

## Usage

Run the generator:

```bash
php artisan make:crud-module User
```

What it creates:

- `UserRepositoryInterface`
- `UserRepository` (Eloquent)
- `UserService`
- `UserController` (API)
- `UserRequest`
- Automatic binding entry in `RepositoryServiceProvider`

## Automatic Binding Example

The command will add a binding similar to:

```php
public function register(): void
{
    $this->app->bind(
        \App\Repositories\Interfaces\UserRepositoryInterface::class,
        \App\Repositories\Eloquent\UserRepository::class
    );
}
```

## Generated Controller Example

```php
class UserController extends Controller
{
    public function __construct(protected UserService $service) {}

    public function index()
    {
        return $this->service->all();
    }

    public function store(UserRequest $request)
    {
        return $this->service->create($request->validated());
    }

    public function show($id)
    {
        return $this->service->find($id);
    }

    public function update(UserRequest $request, $id)
    {
        return $this->service->update($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->service->delete($id);
    }
}
```

## Notes

- The generator aims to reduce boilerplate and enforce a consistent structure.
- Files created are examples — adapt namespaces and signatures to your project needs.

