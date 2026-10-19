<?php


require_once dirname(__DIR__) . '/Repositories/ReservaRepository.php';
class VistaController
{
    private \Twig\Environment $twig;
    private ReservaRepository $repository;
    public function __construct(\Twig\Environment $twig)
    {
        $this->twig = $twig;
        $this->repository = new ReservaRepository();
    }
    public function listar()
    {
        $reservas = $this->repository->filtrar(null, null, null, null);
        echo $this->twig->render('reservas/listado.twig', ['reservas' => $reservas]);
    }
    public function detalle($id)
    {
        $reserva = $this->repository->detalle($id);
        if (!$reserva) {
            http_response_code(404);
            echo $this->twig->render('error.twig', ['mensaje' => 'Reserva no encontrada']);
            return;
        }
        $eventos = $this->repository->obtenerEventos($id);
        echo $this->twig->render('reservas/detalle.twig', ['reserva' => $reserva, 'eventos' => $eventos]);
    }
    public function filtrar($estado, $nombreHuesped, $fechaEntrada, $fechaSalida)
    {
        $reservas = $this->repository->filtrar($estado, $nombreHuesped, $fechaEntrada, $fechaSalida);
        echo $this->twig->render('reservas/listado.twig', ['reservas' => $reservas, 'filtros' => ['estado' => $estado ?? '', 'nombre_huesped' => $nombreHuesped ?? '', 'fecha_entrada' => $fechaEntrada ?? '', 'fecha_salida' => $fechaSalida ?? '']]);
    }
}