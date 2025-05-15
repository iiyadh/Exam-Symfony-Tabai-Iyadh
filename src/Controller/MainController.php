<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class MainController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'product_dashboard')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        return $this->render('product/product-dashboard.html.twig', [
            'page_title' => 'Product Dashboard',
            'products' => $products,
        ]);
    }

    #[Route('/list', name: 'product_list')]
    public function list(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        return $this->render('product/product-list.html.twig', [
            'page_title' => 'All Products',
            'products' => $products,
        ]);
    }

    #[Route('/add', name: 'product_add')]
    public function add(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $price = $request->request->get('price');
            $description = $request->request->get('description');
            $imageUrl = $request->request->get('image_url');

            $product = new Product();
            $product->setName($name);
            $product->setPrice((float)$price);
            $product->setDescription($description);
            $product->setImageUrl($imageUrl);

            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Product added successfully!');
            return $this->redirectToRoute('product_dashboard');
        }

        return $this->render('product/product-add.html.twig', [
            'page_title' => 'Add New Product',
        ]);
    }


    #[Route('/delete/{id}', name: 'product_delete')]
    public function delete(int $id): Response
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        if ($product) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();
            $this->addFlash('success', 'Product deleted successfully!');
        }

        return $this->redirectToRoute('product_dashboard');
    }

    #[Route('/edit/{id}', name: 'product_edit')]
    public function edit(int $id, Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $product = $this->entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found.');
        }

        if ($request->isMethod('POST')) {
            $product->setName($request->request->get('name'));
            $product->setPrice((float) $request->request->get('price'));
            $product->setDescription($request->request->get('description'));
            $product->setImageUrl($request->request->get('image_url'));

            $this->entityManager->flush();

            $this->addFlash('success', 'Product updated successfully!');
            return $this->redirectToRoute('product_dashboard');
        }

        return $this->render('product/product-edit.html.twig', [
            'page_title' => 'Edit Product',
            'product' => $product,
        ]);
    }
}
