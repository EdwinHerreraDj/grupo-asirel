<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Toda la aplicación exige sesión: un invitado que entra en la raíz
     * se redirige al login.
     */
    public function test_invitado_en_la_raiz_va_al_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }
}
