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

use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Galvesband\TraUserBundle\Entity\User;

/**
 * Class RemoveUserCommand
 *
 * Removes an user from the system.
 *
 * @package Galvesband\TraUserBundle\Command
 */
class RemoveUserCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('galvesband:tra-user:remove-user')
            ->setDescription('Removes an user.')
            ->setHelp("This command allows you to remove users.")

            ->addArgument('username', InputArgument::REQUIRED, "The name of the user to be removed.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->getContainer()->get('doctrine')
            ->getRepository('GalvesbandTraUserBundle:User')
            ->findByName($input->getArgument('username'));

        if (is_null($user)) {
            $output->writeln("User {$input->getArgument('username')} not found.");
        } else {
            try {
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->remove($user);
                $em->flush();

                $output->writeln("User {$user->getName()} removed.");
            } catch (Exception $e) {
                $output->writeln("Error while removing user: ".$e->getMessage());
            }
        }
    }
}