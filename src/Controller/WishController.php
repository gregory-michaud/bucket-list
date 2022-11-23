<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\WishRepository;
use App\Services\Censurator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WishController extends AbstractController
{
    #[Route('/wish/list', name: 'wish_list')]
    public function list(WishRepository $repository): Response
    {
        $wishes = $repository->findWishesWithCategory();

        dump($wishes);
        return $this->render('wish/list.html.twig', [
            'wishes' => $wishes
        ]);
    }

    #[Route('/wish/detail/{id}', name: 'wish_detail')]
    public function detail($id, WishRepository $repository): Response
    {
        $wish = $repository->findOneWishWithCategoryById($id);
        return $this->render('wish/detail.html.twig', [
            'wish' => $wish
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/wish/ajouter', name: 'wish_ajouter')]
    public function ajouter(Request $request, EntityManagerInterface $entityManager, Censurator $censurator): Response
    {
        $wish = new Wish();
        $wish->setAuthor($this->getUser()->getUserIdentifier());

        $wishForm = $this->createForm(WishType::class, $wish);

        $wishForm->handleRequest($request);

        if($wishForm->isSubmitted() && $wishForm->isValid()){
            $wish->setIsPublished(true);
            $wish->setDateCreated(new \DateTime());

            $wish->setTitle($censurator->purify($wish->getTitle()));
            $wish->setDescription($censurator->purify($wish->getDescription()));

            $entityManager->persist($wish);
            $entityManager->flush();
            //Idea successfully added!

            $this->addFlash("success", "Idea successfully added!");
            return $this->redirectToRoute('wish_detail', ['id' => $wish->getId()]);

        }

        return $this->render('wish/ajouter.html.twig', [
            'wishForm' => $wishForm->createView()
        ]);
    }

}
