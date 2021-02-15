<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use App\Entity\Setting;
use App\Service\SettingService;

/**
 * @Route("/ustawienia")
 */

class SettingController extends AbstractController
{

    /**
     * @Route("", name="settings")
     */
    public function index(Request $request, SettingService $SettingService)
    {
        $em = $this->getDoctrine()->getManager();

        $settings = $SettingService->list();
        $form = $this->createFormBuilder($settings)
            ->add('email', EmailType::class, [
                'label' => 'Adres e-mail administratora'
            ])
            ->add('title', TextType::class, [
                'required' => false,
                'label' => 'Tytuł strony'
            ])
            ->add('recaptcha_site_key', TextType::class, [
                'required' => false,
                'label' => 'Recaptcha v3 Site Key',
                'help' => '<a href="https://www.google.com/recaptcha/admin#list" target="_blank">google.com/recaptcha/admin</a>',
                'help_html' => true
            ])
            ->add('recaptcha_secret_key', TextType::class, [
                'required' => false,
                'label' => 'Recaptcha v3 Secret Key',
                'data' => $SettingService->get('recaptcha_secret_key'),
            ])
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
           
            $repository = $this->getDoctrine()->getRepository(Setting::class);
            foreach($data as $name => $value){
                if(array_key_exists($name, $settings)){
                    $s = $repository->findOneBy(['name' => $name]);
                }else{
                    $s = new Setting();
                    $s->setName($name);
                }
                $s->setValue($value);
                $em->persist($s);
            }
            $em->flush();

            $this->addFlash('success', 'Pomyślnie zapisano');

        }

        return $this->render('setting/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     *
     * @Route("/command/cache/clear", name="admin_command_cache_clear")
     */
    public function command_cache_clear(Request $request, KernelInterface $kernel)
    {
        if ($this->isCsrfTokenValid('clear-cache', $request->request->get('token'))) {

            $this->do_command($kernel, 'cache:clear');
            
            $this->addFlash('success', 'Cache został pomyślnie usunięty');

            return $this->redirectToRoute('admin_settings');
        }else{
            throw new InvalidCsrfTokenException();
        }
    }

    private function do_command($kernel, $command)
    {
        $env = $kernel->getEnvironment();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => $command,
            '--env' => $env
        ));

        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = $output->fetch();

        return new Response($content);
    }
}
