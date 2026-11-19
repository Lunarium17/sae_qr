<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        $ahoraSae = Carbon::now();
        $horaSae = (int) $ahoraSae->format('H');
        $saludoHorarioSae = $horaSae < 12 ? 'Buenos días' : ($horaSae < 19 ? 'Buenas tardes' : 'Buenas noches');

        $diasEspSae = [
            0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
            4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado',
        ];
        $mesesEspSae = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ];
        $fechaActualEspanolSae = $diasEspSae[(int) $ahoraSae->format('w')] . ', '
            . (int) $ahoraSae->format('d') . ' de '
            . $mesesEspSae[(int) $ahoraSae->format('n')] . ' de '
            . $ahoraSae->format('Y');
        $semestreNumSae = ((int) $ahoraSae->format('n')) <= 6 ? '1' : '2';
        $periodoEscolarActualSae = 'Periodo escolar ' . $ahoraSae->format('Y') . '-' . $semestreNumSae;

        View::share([
            'saludoHorarioSae'        => $saludoHorarioSae,
            'fechaActualEspanolSae'   => $fechaActualEspanolSae,
            'periodoEscolarActualSae' => $periodoEscolarActualSae,
        ]);
    }
}
