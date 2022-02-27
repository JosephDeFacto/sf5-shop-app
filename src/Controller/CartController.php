<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProducts;
use App\Entity\Product;
use App\Repository\CartProductsRepository;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(): Response
    {
        $user = $this->getUser();

        $cartProducts = ($user) ? $user->getCart()->getCartProducts() : null;

        /*dd($cartProducts->getValues());*/


        return $this->render('cart/index.html.twig', ['cartProducts' => $cartProducts]);
    }

    /**
     * @Route("cart/products/{id}", name="cart_add_to", priority=2)
     */
    public function addToCart(ProductRepository $productRepository, ManagerRegistry $registry, Request $request): RedirectResponse
    {
        $em = $registry->getManager();
        $id = $request->attributes->get('id');

        $cartUser = $this->getUser()->getCart();

        $product = $productRepository->find($id);

        $cartProducts = new CartProducts();

        $cartProducts->setCart($cartUser);
        $cartProducts->setProduct($product);


        $em->persist($cartProducts);
        $em->flush();
        $this->addFlash('success', 'Successfully added to cart!');


        return $this->redirectToRoute('home');
    }

    /**
     * @Route("cart/remove/{id}", name="cart_remove_one")
     * @return RedirectResponse
     */
    public function removeFromCart(Request $request, ManagerRegistry $registry, ProductRepository $productRepository, CartProductsRepository $cartProductsRepository)
    {
        $em = $registry->getManager();

        $id = $request->attributes->get('id');

        $products = $cartProductsRepository->findOneBy([
            'cart' => $this->getUser()->getCart(),
            'product' => $productRepository->find($id)
        ]);

        $em->remove($products);
        $em->flush();

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("cart/increment/{id}", name="cart_increment_by_one")
     */
    public function quantityIncrementByOne(ManagerRegistry $registry, Request $request, ProductRepository $productRepository, CartProductsRepository $cartProductsRepository): RedirectResponse
    {
        $id = $request->attributes->get('id');

        $product = $productRepository->find($id);

        $cart = $cartProductsRepository->findOneBy([
            'cart' => $this->getUser()->getCart(),
            'product' => $product
        ]);

        $em = $registry->getManager();

        $cart->setQuantity($cart->getQuantity() + 1);
        $em->persist($cart);
        $em->flush();

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("cart/decrement/{id}", name="cart_decrement_by_one")
     */
    public function quantityDecrementByOne(ManagerRegistry $registry, Request $request, ProductRepository $productRepository, CartProductsRepository $cartProductsRepository): RedirectResponse
    {
        $id = $request->attributes->get('id');

        $product = $productRepository->find($id);

        $cart = $cartProductsRepository->findOneBy([
            'cart' => $this->getUser()->getCart(),
            'product' => $product
        ]);

        $em = $registry->getManager();

        $cart->setQuantity($cart->getQuantity() - 1);
        $em->persist($cart);
        $em->flush();

        return $this->redirectToRoute('cart');
    }

}
