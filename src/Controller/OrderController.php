<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProducts;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Repository\CartProductsRepository;
use App\Repository\OrderProductRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/order", name="order")
     */
    public function index(OrderRepository $orderRepository): Response
    {
        // fetch orders current/logged user
        $orders = $orderRepository->findBy(['user' => $this->getUser()]);


        $countOrders = $orderRepository->countNumberOfOrders();

        return $this->render('order/index.html.twig', ['orders' => $orders, 'countOrders' => $countOrders]);
    }

    /**
     * @Route("order/checkout", name="order_checkout")
     */
    public function orderCheckout(ManagerRegistry $registry)
    {
        $em = $registry->getManager();
        $user = $this->getUser();

        $products = $this->getUser()->getCart()->getCartProducts();

        $order = new Order();

        $order->setUser($user);
        $em->persist($order);
        $em->flush();
        // add ifs and validation
        $orderProducts = new OrderProduct();
        foreach ($products as $product) {
            $orderProducts->setProduct($product->getProduct());
            $orderProducts->setOrders($order);
            $orderProducts->setQuantity($product->getQuantity());
            $em->persist($orderProducts);
            $em->flush();
            $this->clearCart($products);



            return $this->redirectToRoute('home');
        }

        return $this->redirectToRoute('home');
    }

    public function clearCart($products)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($products as $product) {
            $em->remove($product);
            $em->flush();
        }
    }
}
