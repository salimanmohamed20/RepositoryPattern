🚀 Laravel CRUD Module Generator

A powerful Artisan command to automatically generate a full CRUD module following the Repository Design Pattern with clean architecture and best practices.

This tool helps teams:

Reduce boilerplate code

Enforce consistent architecture

Speed up development

Improve onboarding for new developers

✨ Features

Automatically generates:

✅ Repository Interface

✅ Eloquent Repository Implementation

✅ Service Layer

✅ API Controller with full CRUD methods

✅ Form Request extending a shared BaseFormRequest

✅ Automatic Repository Binding in Service Provider

All generated files are fully wired together and ready to use.



🧱 Architecture Overview

This generator enforces the following structure:

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
├── Providers/
│   └── RepositoryServiceProvider.php

⚙️ Installation
1. Create BaseFormRequest (once)
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

2. Create Repository Service Provider
php artisan make:provider RepositoryServiceProvider




3. Add the Artisan Command

Create the command:

php artisan make:command MakeCrudModule


Add the full command implementation to:

app/Console/Commands/MakeCrudModule.php

🛠 Usage

Run the command:

php artisan make:crud-module User


This will generate:

UserRepositoryInterface

UserRepository

UserService

UserController

UserRequest

Automatic binding in RepositoryServiceProvider

🔗 Automatic Binding

The command will automatically add this to:

app/Providers/RepositoryServiceProvider.php


Example:

public function register(): void
{
    $this->app->bind(
        \App\Repositories\Interfaces\UserRepositoryInterface::class,
        \App\Repositories\Eloquent\UserRepository::class
    );
}

🧪 Generated Controller Example
class UserController extends Controller
{
    public function __construct(
        protected UserService $service
    ) {}

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