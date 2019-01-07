<?php

namespace App\Controller;

use App\Entity\SubReddit;
use App\Form\SubRedditType;
use App\Repository\SubRedditRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/subreddit")
 */
class SubRedditController extends AbstractController
{
    /**
     * @Route("/", name="sub_reddit_index", methods={"GET"})
     */
    public function index(SubRedditRepository $subRedditRepository): Response
    {
        return $this->render('sub_reddit/index.html.twig', [
            'sub_reddits' => $subRedditRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="sub_reddit_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $subReddit = new SubReddit();
        $form = $this->createForm(SubRedditType::class, $subReddit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subReddit);
            $entityManager->flush();

            return $this->redirectToRoute('sub_reddit_index');
        }

        return $this->render('sub_reddit/new.html.twig', [
            'sub_reddit' => $subReddit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="sub_reddit_show", methods={"GET"})
     */
    public function show(SubReddit $subReddit): Response
    {
        return $this->render('sub_reddit/show.html.twig', [
            'sub_reddit' => $subReddit,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="sub_reddit_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, SubReddit $subReddit): Response
    {
        $form = $this->createForm(SubRedditType::class, $subReddit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('sub_reddit_index', [
                'id' => $subReddit->getId(),
            ]);
        }

        return $this->render('sub_reddit/edit.html.twig', [
            'sub_reddit' => $subReddit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="sub_reddit_delete", methods={"DELETE"})
     */
    public function delete(Request $request, SubReddit $subReddit): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subReddit->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($subReddit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sub_reddit_index');
    }
}
