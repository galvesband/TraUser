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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Galvesband\TraUserBundle\Entity\User;
use \Galvesband\TraUserBundle\Entity\Group;

/**
 * Class AddUserToGroupCommand
 *
 * Adds a user to a group.
 *
 * @package Galvesband\TraUserBundle\Command
 */
class AddUserToGroupCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->setName('galvesband:tra-user:add-user-to-group')
            ->setDescription('Adds an user to a group.')
            ->setHelp("This command allows you to add an existing user to an existing group.");

        $this
            ->addArgument('user', InputArgument::REQUIRED, "The name of the user.")
            ->addArgument('group', InputArgument::REQUIRED, "The name of the group.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository('GalvesbandTraUserBundle:User')
            ->findByName($input->getArgument('user'));
        $group = $em->getRepository('GalvesbandTraUserBundle:Group')
            ->findByName($input->getArgument('group'));

        if (is_null($user) || is_null($group)) {
            if (is_null($user))
                $output->writeln("User not found: {$input->getArgument('user')}.");
            if (is_null($group))
                $output->writeln("Group not found: {$input->getArgument('group')}.");
        } else if ($user->getGroups()->contains($group)) {
            $output->writeln("User {$user->getName()} is already part of group {$group->getName()}.");
        } else {
            $user->addGroup($group);
            $group->addUser($user);
            $em->flush();
            $output->writeln("User {$user->getName()} is now part of group {$group->getName()}.");
        }
    }
}