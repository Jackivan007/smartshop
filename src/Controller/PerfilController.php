<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\PerfilType;
use App\Form\CambioPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class PerfilController extends AbstractController
{
    #[Route('/perfil', name: 'app_perfil')]
    public function perfil(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        /** @var Usuario|null $usuario */
        $usuario = $this->getUser();

        if (!$usuario) {
            throw $this->createAccessDeniedException('No hay usuario autenticado.');
        }

        $usuarioOriginal = clone $usuario;

        // Formulario de edición de perfil
        $formEditar = $this->createForm(PerfilType::class, $usuario);
        $formEditar->handleRequest($request);

        if ($formEditar->isSubmitted() && $formEditar->isValid()) {
            $emailIntroducido = $formEditar->get('email')->getData();
            $usernameIntroducido = $formEditar->get('username')->getData();

            $emailExistente = $em->getRepository(Usuario::class)->findOneBy(['email' => $emailIntroducido]);
            $userExistente = $em->getRepository(Usuario::class)->findOneBy(['username' => $usernameIntroducido]);

            if ($emailExistente && $emailExistente !== $usuario) {
                $formEditar->get('email')->addError(new FormError('Ya existe una cuenta con este correo electrónico.'));
            }

            if ($userExistente && $userExistente !== $usuario) {
                $formEditar->get('username')->addError(new FormError('Ya existe una cuenta con este nombre de usuario.'));
            }

            if ($formEditar->isValid()) {
                $em->flush();

                // Reautenticar si cambia el identificador principal
                if ($usuarioOriginal->getUserIdentifier() !== $usuario->getUserIdentifier()) {
                    $token = new UsernamePasswordToken($usuario, 'main', $usuario->getRoles());
                    $this->container->get('security.token_storage')->setToken($token);
                }

                $this->addFlash('success', 'Perfil actualizado correctamente.');
                return $this->redirectToRoute('app_perfil');
            }
        }

        // Formulario de cambiar contraseña
        $formPassword = $this->createForm(CambioPasswordType::class);
        $formPassword->handleRequest($request);

        if ($formPassword->isSubmitted() && $formPassword->isValid()) {
            $datos = $formPassword->getData();

            if ($hasher->isPasswordValid($usuario, $datos['currentPassword'])) {
                $usuario->setPassword($hasher->hashPassword($usuario, $datos['newPassword']));
                $em->flush();
                $this->addFlash('success', 'Contraseña actualizada correctamente.');
                return $this->redirectToRoute('app_perfil');
            } else {
                $this->addFlash('error', 'La contraseña actual no es correcta.');
                return $this->redirectToRoute('app_perfil');
            }
        }

        return $this->render('usuario/perfil.html.twig', [
            'formEditar' => $formEditar->createView(),
            'formPassword' => $formPassword->createView(),
        ]);
    }

    #[Route('/perfil/eliminar', name: 'app_eliminar_cuenta', methods: ['POST'])]
    public function eliminarCuenta(
        EntityManagerInterface $em,
        Request $request,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage
    ): Response {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        if ($usuario && $this->isCsrfTokenValid('eliminar_usuario', $request->request->get('_token'))) {

            // Quitar al usuario de los grupos donde está unido como miembro
            foreach ($usuario->getGruposUnidos() as $grupo) {
                $grupo->removeMiembro($usuario);
            }

            // Eliminar el usuario
            $em->remove($usuario);
            $em->flush();

            // Cerrar sesión correctamente
            $tokenStorage->setToken(null);
            $session->invalidate();

            return $this->redirectToRoute('app_login');
        }

        return $this->redirectToRoute('app_perfil');
    }
}
