<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use App\Service\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MediaController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManagerInterface,
        private MediaService $mediaService
    )
    {
    }

    #[Route("/admin/media", name: "admin_media_index")]
    public function index(Request $request)
    {
        $page = $request->query->getInt('page', 1);

        $criteria = [];

        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['user'] = $this->getUser();
        }

        $medias = $this->mediaService->findIndexMedias($page, $criteria);

        $total = $this->mediaService->mediasCount($criteria);

        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page
        ]);
    }

    #[Route("/admin/media/add", name: "admin_media_add")]
    public function add(Request $request)
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->mediaService->addMedia(
                $media,
                !$this->isGranted('ROLE_ADMIN') ? $this->getUser() : null
            );

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/add.html.twig', ['form' => $form->createView()]);
    }

    #[Route("/admin/media/delete/{id}", name: "admin_media_delete")]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Media $media)
    {
        $this->mediaService->deleteMedia($media);

        return $this->redirectToRoute('admin_media_index');
    }
}