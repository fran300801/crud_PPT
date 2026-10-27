<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class crudTest extends TestCase
{
    /**
     * A basic feature test example.
     */


    private function crearUsuario(string $rol = 'user'): User
    {
        return User::forceCreate([
            'name' => 'Usuario de prueba',
            'email' => uniqid() . '@test.com',
            'password' => Hash::make('password123'),
            'rol' => $rol,
        ]);
    }

    public function test_un_usuario_no_autenticado_no_puede_acceder(): void
    {
        $this->getJson('/api/admin/1')->assertUnauthorized();
        $this->postJson('/api/admin', [])->assertUnauthorized();
        $this->deleteJson('/api/admin/1')->assertUnauthorized();
    }

    public function test_un_usuario_normal_no_puede_acceder(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario, 'sanctum');

        $this->getJson('/api/admin/1')->assertForbidden();
        $this->postJson('/api/admin', [])->assertForbidden();
        $this->deleteJson('/api/admin/1')->assertForbidden();
    }

    public function test_un_administrador_puede_crear_un_usuario(): void
    {
        $admin = $this->crearUsuario('admin');

        $this->actingAs($admin, 'sanctum');

        $respuesta = $this->postJson('/api/admin', [
            'name' => 'Nuevo usuario',
            'email' => 'nuevo@test.com',
            'password' => 'password123',
            'rol' => 'user',
        ]);

        $respuesta->assertOk();

        $this->assertDatabaseHas('users', [
            'name' => 'Nuevo usuario',
            'email' => 'nuevo@test.com',
            'rol' => 'user',
        ]);

        $usuario = User::where('email', 'nuevo@test.com')->first();

        $this->assertNotEquals('password123', $usuario->password);
        $this->assertTrue(Hash::check('password123', $usuario->password));
    }

    public function test_no_se_puede_crear_un_usuario_con_datos_invalidos(): void
    {
        $admin = $this->crearUsuario('admin');

        $this->actingAs($admin, 'sanctum');

        $this->postJson('/api/admin', [
            'name' => '',
            'email' => 'correo-invalido',
            'password' => '123',
            'rol' => '',
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('users', [
            'email' => 'correo-invalido',
        ]);
    }

    public function test_un_administrador_puede_consultar_un_usuario(): void
    {
        $admin = $this->crearUsuario('admin');
        $usuario = $this->crearUsuario();

        $this->actingAs($admin, 'sanctum');

        $this->getJson('/api/admin/' . $usuario->id)
            ->assertOk()
            ->assertJsonPath('id', $usuario->id)
            ->assertJsonPath('email', $usuario->email);
    }

    public function test_un_administrador_puede_eliminar_un_usuario(): void
    {
        $admin = $this->crearUsuario('admin');
        $usuario = $this->crearUsuario();

        $this->actingAs($admin, 'sanctum');

        $this->deleteJson('/api/admin/' . $usuario->id)
            ->assertOk();

        $this->assertSoftDeleted('users', [
            'id' => $usuario->id,
        ]);
    }
}
