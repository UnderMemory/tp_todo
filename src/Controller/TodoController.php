<?php

namespace App\Controller;

use App\Entity\Todo;
use App\Form\TodoFormType;
use App\Repository\TodoRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use Symfony\Component\HttpFoundation\Request;

class TodoController extends AbstractController
{
    /**
     * @Route("/todo", name="app_todo")
     */
    public function index(TodoRepository $repo): Response
    {
        $todos = $repo->findAll();
        // dd($todos);
        return $this->render('todo/index.html.twig', ['todos' => $todos]);
    }

        /**
     * @Route("/detail{id}", name="app_todo_detail")
     */
    public function detail($id, TodoRepository $repo): Response
    {
        $todo = $repo->find($id);
        // dd($todos);
        return $this->render('todo/detail.html.twig', 
            ['todo' => $todo]);
    }

    /**
     * @Route("/todo/create", name="app_todo_create", methods= {"GET", "POST"})
     *
     * @return void
     */
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        #Etape d'affichage (GET)
        $todo = new Todo;
        $form = $this->createForm(TodoFormType::class, $todo);

        #Etape soumission (POST)
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            //Méthode que l'on trouve dans des anciennes ressources :
            // $this->getDoctrine()->getManager()->persist($todo);
            $em -> persist($todo);
            $em -> flush();
            return $this->redirectToRoute('app_todo');
        }
        return $this->render('todo/create.html.twig', 
            ['formTodo' => $form->createView()]);
    }

    /**
     * paramconverter => correspondance entre un id dans la route et un objet du type Todo
     * @Route("/detail{id}/update", name="app_todo_update", methods= {"GET", "POST"})
     */
    public function update(Todo $todo, Request $request, EntityManagerInterface $em): Response
    {
        // dd($todo);
        $form = $this->createForm(TodoFormType::class, $todo);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            # Update
            $todo->setUpdatedAt(new \DateTime('now'));
            $em->flush();
            # Création d'un message Flash (session flash)
            $this->addFlash(
                'info',
                'Modification enregistrée avec succès !'
            );
            # On revient sur la même page (methode GET)
            return $this->redirectToRoute('app_todo_update', ['id' => $todo->getId()]);
        }
        return $this->render('todo/update.html.twig', 
            ['formTodo' => $form->createView(),
            'todo' => $todo]);
    }

    /**
     * @Route("/todo/{id}/delete", name="app_todo_delete")
     *
     * @param Todo $todo
     * @param EntityManagerInterface $em
     * @return void
     */
    public function delete(Todo $todo, EntityManagerInterface $em){
        $em->remove($todo);
        $em->flush();
        return $this->redirectToRoute('app_todo');
    }

    /**
     * @Route("/todo/{id}/deletecsrf", name="app_todo_delete_csrf", methods={"DELETE"})
     *
     * @param Todo $todo
     * @param EntityManagerInterface $em
     * @return void
     * 
     * $request->request->get()  :  POST
     * $request->query->get()    :  GET
     */
    public function delete2(Todo $todo, EntityManagerInterface $em, Request $request){
        $submittedToken = $request->request->get('token');
        // dd($submittedToken);
        if($this->isCsrfTokenValid('delete-item', $submittedToken)){
            $em->remove($todo);
            $em->flush();
        }
        
        return $this->redirectToRoute('app_todo');
    }

}