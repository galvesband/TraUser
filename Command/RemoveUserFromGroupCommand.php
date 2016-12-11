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
 * Class RemoveUserFromGroupCommand
 *
 * Removes an user from a group.
 *
 * @package Galvesband\TraUserBundle\Command
 */
class RemoveUserFromGroupCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->setName('galvesband:tra-user:remove-user-from-group')
            ->setDescription('Removes an user to a group.')
            ->setHelp("This command allows you to remove an existing user from an existing group he is part of.")

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
        } else if (!$user->getGroups()->contains($group) || !$group->getUsers()->contains($user)) {
            $output->writeln("User {$user->getName()} is not part of group {$group->getName()}.");
        } else {
            $user->removeGroup($group);
            $group->removeUser($user);
            $em->flush();
            $output->writeln("User {$user->getName()} has been removed from group {$group->getName()}.");
        }
    }
}