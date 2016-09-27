<?php

/*
 * This file is part of the Galvesband TraUserBundle.
 *
 * (c) Rafael Gálvez-Cañero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Galvesband\TraUserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Galvesband\TraUserBundle\Entity\User;

/**
 * Class AddCommand
 *
 * Adds a new user to the system.
 *
 * @package Galvesband\TraUserBundle\Command
 */
class AddUserCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('galvesband_tra_user:add_command')
            ->setDescription('Adds a new user.')
            ->setHelp("This command allows you to create users.")

            ->addArgument('username', InputArgument::REQUIRED, "The name of the new user.")
            ->addArgument('email', InputArgument::REQUIRED, "The email of the user")
            ->addArgument('password', InputArgument::REQUIRED, "The password of the new user")
            ->addOption("inactive", "i", InputOption::VALUE_NONE, "Sets the new user as inactive.")
            ->addOption("super", "s", InputOption::VALUE_NONE, "Sets the new user as super-administrator.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = new User();
        $user->setName($input->getArgument('username'));
        $user->setEmail($input->getArgument('email'));
        $user->setPlainPassword($input->getArgument('password'));
        $user->setIsActive(!$input->getOption('inactive'));
        $user->setIsSuperAdmin($input->getOption('super'));

        // TODO Validation of User class

        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        $output->writeln('User created: ' . $user->getName());
    }
}
