<?php

namespace App\Controller\Admin;

use App\Entity\Allergen;
use App\Form\AllergenType;
use App\Repository\AllergenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AllergenController extends AbstractController
{
    /**
     * //* @Route("/admin/allergens", name="admin_allergens_list")
     * @param AllergenRepository $allergenRepository
     * @return Response
     */

    public function allergens(AllergenRepository $allergenRepository)
    {
        $allergens = $allergenRepository->findAll();
        return $this->render('admin/allergens/allergens.html.twig', [
            'allergens' => $allergens,
        ]);
    }

    /**
     * @route("admin/allergen/show/{id}", name="admin_allergen_show")
     * @param AllergenRepository $allergenRepository
     * @param $id
     * @return Response
     */
    public function allergen(AllergenRepository $allergenRepository, $id)
    {
        $allergen = $allergenRepository->find($id);

        return $this->render('admin/allergens/allergen.html.twig', [
            'allergen' => $allergen,
        ]);
    }

    /**
     * @route("admin/allergen/insert", name="admin_allergen_insert")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param $slugger
     * @return Response
     */

    public function insertAllergen(Request $request,
                                   EntityManagerInterface $entityManager,
                                   SluggerInterface $slugger
    )
    {

        $allergen = new Allergen();
        $formAllergen = $this->createForm(AllergenType::class, $allergen);
        $formAllergen->handleRequest($request);


        if ($formAllergen->isSubmitted() && $formAllergen->isValid()) {

            $entityManager->persist($allergen);
            $entityManager->flush();

            $this->addFlash('success', "Le nouveau type d'allergène a bien été créé !");

        }
        return $this->render('admin/allergens/allergen_insert.html.twig', [
            'formAllergen' => $formAllergen->createView()

        ]);

    }

    /**
     * @route("admin/allergen/search", name="admin_allergen_search")
     * @param AllergenRepository $allergenRepository
     * @param Request $request
     * @return Response
     */
    public function searchByAllergen(AllergenRepository $allergenRepository, Request $request)
    {
        $search = $request->query->get('search');
        $allergens = $allergenRepository->getByWordInAllergen($search);

        return $this->render('admin/allergens/allergen_search.html.twig', [
            'search' => $search, 'allergens' => $allergens
        ]);
    }

    /**
     * @route("admin/allergen/update/{id}", name="admin_allergen_update")
     * @param Request $request
     * @param AllergenRepository $allergenRepository
     * @param EntityManagerInterface $entityManager
     * @param $id
     * @return Response
     */
    public function updateAllergen(
        Request $request,
        AllergenRepository $allergenRepository,
        EntityManagerInterface $entityManager,
        $id
    )
    {
        $allergen = $allergenRepository->find($id);
        $formAllergen = $this->createForm(AllergenType::class, $allergen);
        $formAllergen->handleRequest($request);
        if ($formAllergen->isSubmitted() && $formAllergen->isValid()) {
            $entityManager->persist($allergen);
            $entityManager->flush();

            $this->addFlash('success', "Le type d'allergène a bien été modifié !");
        }

        return $this->render('admin/allergens/allergen_insert.html.twig', [
            'formAllergen' => $formAllergen->createView()
        ]);
    }

    /**
     * @route("admin/allergen/delete/{id}", name="admin_allergen_delete")
     * @param AllergenRepository $allergenRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function deleteAllergen(
        Request $request,
        AllergenRepository $allergenRepository,
        EntityManagerInterface $entityManager,
        $id
    )
    {
        $allergen = $allergenRepository->find($id);
        $entityManager->remove($allergen);
        $entityManager->flush();

        return $this->render('admin/allergens/allergen_delete.html.twig', [
            'allergen' => $allergen
        ]);
    }

}