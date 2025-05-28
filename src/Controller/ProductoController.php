<?php

namespace App\Controller;

use App\Entity\Producto;
use App\Entity\Lista;
use App\Form\ProductoType;
use App\Repository\CategoriaRepository;
use App\Repository\ListaRepository;
use App\Repository\ProductoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductoController extends AbstractController
{
    #[Route('/listas/{listaId}/items', name: 'app_items')]
    public function index(
        int $listaId,
        ListaRepository $listaRepo,
        ProductoRepository $productoRepo,
        CategoriaRepository $categoriaRepo
    ): Response {
        $lista = $listaRepo->find($listaId);
        if (!$lista) {
            throw $this->createNotFoundException('Lista no encontrada.');
        }

        $items = $productoRepo->findBy(['lista' => $lista]);
        $categorias = $categoriaRepo->findAll();

        return $this->render('producto/productos.html.twig', [
            'lista' => $lista,
            'items' => $items,
            'categorias' => array_map(fn($cat) => $cat->getNombre(), $categorias),
        ]);
    }

    #[Route('/listas/{listaId}/items/nuevo', name: 'app_item_nuevo')]
    public function nuevo(
        int $listaId,
        ListaRepository $listaRepo,
        CategoriaRepository $categoriaRepo,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $lista = $listaRepo->find($listaId);
        if (!$lista) {
            throw $this->createNotFoundException('Lista no encontrada.');
        }

        $producto = new Producto();
        $producto->setLista($lista);
        $producto->setComprado(false);
        $producto->setCantidad(1);

        $categoriaPorDefecto = $categoriaRepo->findOneBy(['nombre' => 'Sin categoría']);
        if ($categoriaPorDefecto) {
            $producto->setCategoria($categoriaPorDefecto);
        }

        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($producto->getCategoria() === null && $categoriaPorDefecto) {
                $producto->setCategoria($categoriaPorDefecto);
            }

            $em->persist($producto);
            $em->flush();

            return $this->redirectConRecarga($this->generateUrl('app_items', ['listaId' => $listaId]));
        }

        return $this->render('producto/crear.html.twig', [
            'form' => $form->createView(),
            'lista' => $lista,
        ]);
    }

    #[Route('/items/{id}/editar', name: 'app_item_editar')]
    public function editar(
        int $id,
        ProductoRepository $productoRepo,
        CategoriaRepository $categoriaRepo,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $producto = $productoRepo->find($id);
        if (!$producto) {
            throw $this->createNotFoundException('Item no encontrado.');
        }

        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($producto->getCategoria() === null) {
                $categoriaPorDefecto = $categoriaRepo->findOneBy(['nombre' => 'Sin categoría']);
                if ($categoriaPorDefecto) {
                    $producto->setCategoria($categoriaPorDefecto);
                }
            }

            $em->flush();

            return $this->redirectConRecarga($this->generateUrl('app_items', ['listaId' => $producto->getLista()->getId()]));
        }

        return $this->render('producto/editar.html.twig', [
            'form' => $form->createView(),
            'lista' => $producto->getLista(),
        ]);
    }

    #[Route('/items/{id}/eliminar', name: 'app_item_eliminar', methods: ['POST'])]
    public function eliminar(
        int $id,
        Request $request,
        ProductoRepository $productoRepo,
        EntityManagerInterface $em
    ): Response {
        $producto = $productoRepo->find($id);

        if (!$producto) {
            $listaId = $request->query->get('listaId');
            if ($listaId) {
                return $this->redirectConRecarga($this->generateUrl('app_items', ['listaId' => $listaId]));
            }
            return $this->redirectToRoute('app_grupos');
        }

        if ($this->isCsrfTokenValid('eliminar_item_' . $producto->getId(), $request->request->get('_token'))) {
            $em->remove($producto);
            $em->flush();
        }

        return $this->redirectConRecarga($this->generateUrl('app_items', ['listaId' => $producto->getLista()->getId()]));
    }

    #[Route('/listas/{listaId}/items/desmarcar', name: 'app_item_desmarcar_todos', methods: ['POST'])]
    public function desmarcarTodos(
        int $listaId,
        ProductoRepository $productoRepo,
        ListaRepository $listaRepo,
        EntityManagerInterface $em
    ): Response {
        $lista = $listaRepo->find($listaId);
        if (!$lista) {
            throw $this->createNotFoundException('Lista no encontrada.');
        }

        $items = $productoRepo->findBy(['lista' => $lista, 'comprado' => true]);

        foreach ($items as $item) {
            $item->setComprado(false);
        }

        $em->flush();

        return $this->redirectConRecarga($this->generateUrl('app_items', ['listaId' => $listaId]));
    }

    #[Route('/items/{id}/toggle', name: 'app_item_toggle', methods: ['POST'])]
    public function toggleComprado(
        int $id,
        Request $request,
        ProductoRepository $productoRepo,
        EntityManagerInterface $em
    ): Response {
        $producto = $productoRepo->find($id);
        if (!$producto) {
            return $this->json(['error' => 'Item no encontrado'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['comprado'])) {
            return $this->json(['error' => 'Datos inválidos'], 400);
        }

        $producto->setComprado($data['comprado']);
        $em->flush();

        return $this->json(['success' => true]);
    }

    private function redirectConRecarga(string $ruta): Response
    {
        return new Response(
            '<html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($ruta) . '"></head><body></body></html>',
            302
        );
    }
}
