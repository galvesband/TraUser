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
use \Galvesband\TraUserBundle\Entity\Group;
use \Galvesband\TraUserBundle\Entity\Role;

/**
 * Class AddGroupToRoleCommand
 *
 * Adds a group to a role.
 *
 * @package Galvesband\TraUserBundle\Command
 */
class RemoveRoleFromGroupCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->setName('galvesband:tra-user:remove-role-from-group')
            ->setDescription('Removes a role to a group.')
            ->setHelp("This command allows you to remove an existing role from an existing group.");

        $this
            ->addArgument('role', InputArgument::REQUIRED, "The name of the role.")
            ->addArgument('group', InputArgument::REQUIRED, "The name of the group.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $group = $em->getRepository('GalvesbandTraUserBundle:Group')
            ->findByName($input->getArgument('group'));
        $role = $em->getRepository('GalvesbandTraUserBundle:Role')
            ->findByName($input->getArgument('role'));

        if (is_null($role) || is_null($group)) {
            if (is_null($group))
                $output->writeln("Group not found: {$input->getArgument('group')}.");
            if (is_null($role))
                $output->writeln("Role not found: {$input->getArgument('role')}.");
        } else if (!$role->getGroups()->contains($group)) {
            $output->writeln("Group {$group->getName()} does not have the role {$role->getName()}.");
        } else {
            $role->removeGroup($group);
            $group->removeRole($role);
            $em->flush();
            $output->writeln("Role {$role->getName()} has been removed from group {$group->getName()}.");
        }
    }
}