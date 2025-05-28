<?php

namespace App\Controller;

use App\Entity\Grupo;
use App\Entity\Lista;
use App\Form\ListaType;
use App\Repository\GrupoRepository;
use App\Repository\ListaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ListaController extends AbstractController
{
    #[Route('/grupos/{grupoId}/listas', name: 'app_listas')]
    public function index(int $grupoId, GrupoRepository $grupoRepo, ListaRepository $listaRepo, Request $request): Response
    {
        $grupo = $grupoRepo->find($grupoId);

        if (!$grupo) {
            throw $this->createNotFoundException('Grupo no encontrado.');
        }

        if (!$grupo->getMiembros()->contains($this->getUser())) {
            return $this->redirectToRoute('app_grupos');
        }

        // Si NO hay query string ?_recarga=1, redirige con recarga forzada
        if (!$request->query->getBoolean('_recarga')) {
            return $this->redirectConRecarga('app_listas', ['grupoId' => $grupoId, '_recarga' => 1]);
        }

        $listas = $listaRepo->findBy(['grupo' => $grupo], ['createdAt' => 'DESC']);

        return $this->render('lista/listas.html.twig', [
            'grupo' => $grupo,
            'listas' => $listas,
        ]);
    }

    #[Route('/grupos/{grupoId}/listas/nueva', name: 'app_lista_nueva')]
    public function nueva(int $grupoId, GrupoRepository $grupoRepo, Request $request, EntityManagerInterface $em): Response
    {
        $grupo = $grupoRepo->find($grupoId);
        if (!$grupo) {
            throw $this->createNotFoundException('Grupo no encontrado.');
        }

        $lista = new Lista();
        $lista->setGrupo($grupo);
        $lista->setUsuario($this->getUser());
        $lista->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(ListaType::class, $lista);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lista);
            $em->flush();

            return $this->redirectToRoute('app_listas', ['grupoId' => $grupo->getId()]);
        }

        return $this->render('lista/crear.html.twig', [
            'listaForm' => $form->createView(),
            'grupo' => $grupo
        ]);
    }

    #[Route('/listas/{id}/editar', name: 'app_lista_editar')]
    public function editar(int $id, ListaRepository $listaRepo, Request $request, EntityManagerInterface $em): Response
    {
        $lista = $listaRepo->find($id);
        if (!$lista) {
            throw $this->createNotFoundException('Lista no encontrada.');
        }

        $form = $this->createForm(ListaType::class, $lista);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_listas', ['grupoId' => $lista->getGrupo()->getId()]);
        }

        return $this->render('lista/editar.html.twig', [
            'listaForm' => $form->createView(),
            'lista' => $lista,
        ]);
    }

    #[Route('/listas/{id}/eliminar', name: 'app_lista_eliminar', methods: ['POST'])]
    public function eliminar(int $id, Request $request, ListaRepository $listaRepo, EntityManagerInterface $em): Response
    {
        $lista = $listaRepo->find($id);

        if (!$lista) {
            throw $this->createNotFoundException('Lista no encontrada.');
        }

        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('eliminar_lista_' . $lista->getId(), $token)) {
            $em->remove($lista);
            $em->flush();
        }

        return $this->redirectToRoute('app_listas', [
            'grupoId' => $lista->getGrupo()->getId(),
        ]);
    }

    #[Route('/grupo/{grupoId}/expulsar/{userId}', name: 'app_grupo_expulsar', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function expulsarMiembro(
        int $grupoId,
        int $userId,
        GrupoRepository $grupoRepo,
        UsuarioRepository $userRepo,
        EntityManagerInterface $em
    ): Response {
        $grupo = $grupoRepo->find($grupoId);
        $usuario = $userRepo->find($userId);

        if (!$grupo || !$usuario) {
            throw $this->createNotFoundException();
        }

        if ($grupo->getCreadoPor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Solo el creador del grupo puede expulsar miembros.');
        }

        if ($usuario === $this->getUser()) {
            return $this->redirectToRoute('app_listas', ['grupoId' => $grupoId]);
        }

        $grupo->removeMiembro($usuario);
        $usuario->removeGruposUnido($grupo);

        $em->flush();

        return $this->redirectToRoute('app_listas', ['grupoId' => $grupoId]);
    }

    private function redirectConRecarga(string $ruta, array $parametros = []): Response
    {
        $url = $this->generateUrl($ruta, $parametros);

        return new Response(
            '<html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url) . '"></head><body></body></html>',
            302
        );
    }
}
