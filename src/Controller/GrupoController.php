<?php

namespace App\Controller;

use App\Entity\Grupo;
use App\Form\GrupoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GrupoController extends AbstractController
{
    #[Route('/grupos', name: 'app_grupos')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();

        // Grupos que ha creado el usuario
        $gruposCreados = $entityManager->getRepository(Grupo::class)->findBy(
            ['creadoPor' => $usuario],
            ['createdAt' => 'DESC']
        );

        // Grupos donde es miembro pero no creador
        $qb = $entityManager->getRepository(Grupo::class)->createQueryBuilder('g');
        $gruposUnido = $qb
            ->join('g.miembros', 'm')
            ->where('m = :usuario')
            ->andWhere('g.creadoPor != :usuario')
            ->setParameter('usuario', $usuario)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('grupo/grupos.html.twig', [
            'gruposCreados' => $gruposCreados,
            'gruposUnido' => $gruposUnido,
        ]);
    }

    #[Route('/grupos/crear', name: 'app_grupo_crear')]
    public function crearGrupo(Request $request, EntityManagerInterface $entityManager): Response
    {
        $grupo = new Grupo();

        $form = $this->createForm(GrupoType::class, $grupo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Generar clave aleatoria única
            do {
                $clave = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
                $claveExiste = $entityManager->getRepository(Grupo::class)->findOneBy(['clave' => $clave]);
            } while ($claveExiste);

            $grupo->setClave($clave);

            // Asignar creador y miembro
            $grupo->setCreadoPor($this->getUser());
            $grupo->addMiembro($this->getUser());

            // Guardar en la base de datos
            $entityManager->persist($grupo);
            $entityManager->flush();

            return $this->redirectToRoute('app_grupos');
        }

        return $this->render('grupo/crear.html.twig', [
            'grupoForm' => $form->createView(),
        ]);
    }

    #[Route('/grupos/unirse', name: 'app_grupo_unirse')]
    public function unirseGrupo(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder()
            ->add('clave', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => 'Introduce la clave del grupo'
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $clave = $form->get('clave')->getData();

            // Buscar el grupo por la clave
            $grupo = $entityManager->getRepository(Grupo::class)->findOneBy(['clave' => $clave]);

            if (!$grupo) {
                // Si el grupo no existe, añadir flash y redirigir
                $this->addFlash('error', 'Clave incorrecta, intenta de nuevo.');
                return $this->redirectToRoute('app_grupo_unirse');  // Redirigir al formulario de unirse
            } else {
                // Verificar si el usuario ya es miembro
                if ($grupo->getMiembros()->contains($this->getUser())) {
                    // Si el usuario ya forma parte del grupo, añadir flash y redirigir
                    $this->addFlash('error', 'Ya formas parte de este grupo.');
                    return $this->redirectToRoute('app_grupo_unirse');  // Redirigir al formulario de unirse
                } else {
                    // Añadir el usuario como miembro
                    $grupo->addMiembro($this->getUser());

                    // Guardar los cambios en la base de datos
                    $entityManager->flush();

                    // Redirigir al usuario a la página de grupos
                    return $this->redirectToRoute('app_grupos');
                }
            }
        }

        // Renderizar la vista para unirse a un grupo
        return $this->render('grupo/unirse.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/grupos/{id}/editar', name: 'app_grupo_editar')]
    public function editarGrupo(Request $request, Grupo $grupo, EntityManagerInterface $entityManager): Response
    {
        if ($grupo->getCreadoPor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes editar este grupo.');
        }

        $form = $this->createForm(GrupoType::class, $grupo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Grupo editado correctamente.');
            return $this->redirectToRoute('app_grupos');
        }

        return $this->render('grupo/editar.html.twig', [
            'grupoForm' => $form->createView(),
        ]);
    }

    #[Route('/grupos/{id}/eliminar', name: 'app_grupo_eliminar', methods: ['POST'])]
    public function eliminarGrupo(Request $request, Grupo $grupo, EntityManagerInterface $entityManager): Response
    {
        if ($grupo->getCreadoPor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes eliminar este grupo.');
        }

        if ($this->isCsrfTokenValid('eliminar_grupo_' . $grupo->getId(), $request->request->get('_token'))) {
            $entityManager->remove($grupo);
            $entityManager->flush();
            $this->addFlash('success', 'Grupo eliminado correctamente.');
        }

        return $this->redirectToRoute('app_grupos');
    }

    #[Route('/grupos/{id}/abandonar', name: 'app_grupo_abandonar', methods: ['POST'])]
    public function abandonarGrupo(Request $request, Grupo $grupo, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();

        if ($grupo->getCreadoPor() === $usuario) {
            $this->addFlash('error', 'No puedes abandonar un grupo que has creado.');
            return $this->redirectToRoute('app_grupos');
        }

        if ($this->isCsrfTokenValid('abandonar_grupo_' . $grupo->getId(), $request->request->get('_token'))) {
            $grupo->removeMiembro($usuario);
            $entityManager->flush();
            $this->addFlash('success', 'Has abandonado el grupo.');
        }

        return $this->redirectToRoute('app_grupos');
    }
}
