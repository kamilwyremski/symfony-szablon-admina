<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\UserMultipleFormType;
use App\Entity\User;

/**
 * @Route("/uzytkownicy")
 */

class UserController extends AbstractController
{
    private $roles = [];

    public function __construct(RoleHierarchyInterface $rolehierarchy)
    {
        $roles = array();
        array_walk_recursive($rolehierarchy, function($val) use (&$roles) {
            $roles[$val] = $val;
        });
        ksort($roles);
        $this->roles = array_unique($roles);
    }

    /**
     * @Route("", name="user_list")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        $em = $this->getDoctrine()->getManager();
        $repoUser = $this->getDoctrine()->getRepository(User::class);

        $form = $this->createForm(UserMultipleFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            if(!empty($data['ids'])){
                $alert = false;
                if($data['type'] == 'remove_users'){
                    foreach($data['ids'] as $id){
                        if($this->getUser()->getId() == $id){
                            $this->addFlash('danger', 'Nie możesz usunąć samego siebie');
                        }else{
                            $user = $repoUser->find($id);
                            if ($user) {
                                $em->remove($user);
                                $alert = true;
                            }
                        }
                    }
                    $em->flush();
                    if($alert){
                        $this->addFlash('notice', 'Pomyślnie usunięto');
                    }
                }
            }else{
                $this->addFlash('danger','Zaznacz minimum jedną pozycję');
            }
            $form = $this->createForm(UserMultipleFormType::class);
        }

        $findBy = [
            'username' => $request->query->get('username'),
            'email' => $request->query->get('email'),
            'is_active' => $request->query->get('is_active'),
            'role' => $request->query->get('role'),
            'date_from' => $request->query->get('date_from'),
            'date_to' => $request->query->get('date_to'),
            'register_ip' => $request->query->get('register_ip'),
        ];

        $users = $paginator->paginate(
            $repoUser->listQuery($findBy),
            $request->query->getInt('page', 1),
            100,
            [
                'defaultSortFieldName' => 'u.id',
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render('user/list.html.twig', [
            'users' => $users,
            'findBy' => $findBy,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dodaj", name="user_add")
     * @Route("/edytuj/{id}", name="user_edit", requirements={"id"="\d+"})
     */
    public function edit(int $id = null, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {

        if($id){
            $user = $this->getDoctrine()->getRepository(User::class)->find($id);
            if (!$user) {
                throw $this->createNotFoundException('No user found for id '.$id);
            }
        }else{
            $user = new User();
            $user->setIsActive(true);
            $user->setActivationDate(new \DateTime());
        }      

        $form = $this->createFormBuilder($user)
            ->add('username', TextType::class,[
                'attr' => ['autofocus' => true],
            ])
            ->add('email', EmailType::class);

        if($this->getUser()->getId() != $id){
            $form = $form
                ->add('isActive', CheckboxType::class, [
                    'label' => 'Aktywny użytkownik',
                    'required' => false,
                    'label_attr' => ['class' => 'checkbox-custom'],
                ])
                ->add('roles', ChoiceType::class, [
                    'choices' => $this->roles,
                    'required' => true,
                    'multiple' => true,
                ]);
        }
        
        $form = $form
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => $id ? false : true,
                'first_options' => [
                    'constraints' => [
                        new Length([
                            'min' => 4,
                            'minMessage' => 'Hasło powinno mieć minimum {{ limit }} znaków',
                            'max' => 4096,
                        ]),
                    ],
                    'label' => 'Nowe hasło',
                    'help' => $id ? 'Wpisz tylko jeśli chcesz zmienić' : '',
                ],
                'second_options' => [
                    'label' => 'Powtórz hasło',
                ],
                'invalid_message' => 'Hasła muszą być takie same',
                'mapped' => false,
            ])
            ->add('save', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            if($form->get('plainPassword')->getData()){
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
    
            if($id){
                $this->addFlash('success', 'Pomyślnie zaktualizowano');
            }else{
                $this->addFlash('success', 'Pomyślnie dodano');
            }

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/usun/{id}", name="user_delete", requirements={"id"="\d+"})
     */
    public function delete(int $id, Request $request)
    {

        if ($this->isCsrfTokenValid('delete-user', $request->request->get('token'))) {

            if($this->getUser()->getId() == $id){
                
                $this->addFlash('danger', 'Nie możesz usunąć samego siebie');

                return $this->redirectToRoute('admin_user_list');
            }

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->find($id);

            if (!$user) {
                throw $this->createNotFoundException('No user found for id '.$id);
            }

            $em->remove($user);
            $em->flush();
    
            $this->addFlash('notice', 'Pomyślnie usunięto');

            return $this->redirectToRoute('user_list');
        }else{
            throw $this->createException('Invalid token');
        }
    }
}
