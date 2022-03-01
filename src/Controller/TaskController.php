<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

use App\Entity\User;
use App\Entity\Task;
use App\Form\TaskType;

class TaskController extends AbstractController
{
    
    public function index(ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $task_repo = $doctrine->getRepository(Task::class);
        $tasks = $task_repo->findBy([], ['priority' => 'DESC']);
        
        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    public function create(ManagerRegistry $doctrine, Request $request, UserInterface $user): Response
    {
        if(!$user)
        {
            return $this->redirectToRoute('login');
        }
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $task->setCreatedAt(new \Datetime('now'));
            $task->setUser($user);
            $em = $doctrine->getManager();
            $em->persist($task);
            $em->flush();    
        }
        return $this->render('task/create.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    public function detail(Task $task): Response
    {
        if(!$task)  return $this->redirectToRoute('login');
        
        return $this->render('task/detail.html.twig', [
            'task' => $task,
        ]);
    }
    public function myTasks(UserInterface $user){
        $tasks = $user->getTasks();

        return $this->render('task/my-task.html.twig',[
            'tasks' => $tasks
        ]);
    }
    function edit(ManagerRegistry $doctrine, Request $request, UserInterface $user, Task $task): Response
    {
        if(!$user || $user->getId() != $task->getUser()->getId())return $this->redirectToRoute('tasks');
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            //$task->setCreatedAt(new \Datetime('now'));
            //$task->setUser($user);
            $em = $doctrine->getManager();
            $em->persist($task);
            $em->flush();    
        }
        return $this->render('task/create.html.twig', [
            'edit' => True,
            'form'=>$form->createView()
        ]);
    }
    function deleteTask(ManagerRegistry $doctrine, UserInterface $user, Task $task): Response
    {
        if(!$user || $user->getId() != $task->getUser()->getId())return $this->redirectToRoute('tasks');
        if(!$task)return $this->redirectToRoute('tasks');

        $em = $doctrine->getManager();
        $em->remove($task);
        $em->flush(); 

        return $this->redirectToRoute('tasks');
    }
}
