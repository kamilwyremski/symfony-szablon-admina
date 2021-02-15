<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\SettingService;
use App\Entity\User;
use App\Entity\Setting;

class CreateUserCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-user';

    private $setting;
    private $em;
    private $passwordEncoder;
  
    public function __construct(SettingService $setting, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
      $this->setting = $setting;
      $this->em = $em;
      $this->passwordEncoder = $passwordEncoder;
      parent::__construct();
    }
    
    protected function configure()
    {
      $this
        ->setDescription('Create user with role admin')
        ->setHelp('This command create user with role admin and set admin email if no exist');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

      $helper = $this->getHelper('question');

      $username = '';
      $question = new Question("Enter username: ");
      do{
        if($username){
          $output->writeln('Username '.$username.' already exists in the database');
        }
        $username = $helper->ask($input, $output, $question);
      }while(!$username or $this->em->getRepository(User::class)->findOneBy(['username'=>$username]));

      $email = '';
      $question = new Question("Enter email: ");
      do{
        if($email){
          $output->writeln('Email '.$email.' already exists in the database');
        }
        $email = $helper->ask($input, $output, $question);
      }while(!$email or $this->em->getRepository(User::class)->findOneBy(['email'=>$email]));

      $question = new Question("Enter password: ", "guest");
      $question->setHidden(true);
      $question->setHiddenFallback(false);
      do{
        $password = $helper->ask($input, $output, $question);
      }while(!$password);

      $user = new User();
      $user->setUsername($username);
      $user->setEmail($email);
      $user->setPassword(
        $this->passwordEncoder->encodePassword(
            $user,
            $password
        )
      );
      $user->setRoles(['ROLE_USER','ROLE_MODERATOR','ROLE_ADMIN']);
      $user->setIsActive(true);
      $this->em->persist($user);
      $this->em->flush();

      if(!$this->setting->get('email')){
        $s = $this->em->getRepository(Setting::class)->findOneBy(['name' => 'email']);
        $s->setValue($email);
        $this->em->persist($s);
        $this->em->flush();
      }

      $output->writeln('User '.$username.' '.$email.' was successfully created');

      return Command::SUCCESS;
    }
}