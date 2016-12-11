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
use \Galvesband\TraUserBundle\Entity\Role;

/**
 * Class RemoveRoleCommand
 *
 * Removes a role from the system.
 *
 * @package Galvesband\TraUserBundle\Command
 */
class RemoveRoleCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('galvesband:tra-user:remove-role')
            ->setDescription("Removes a role.")
            ->setHelp("This command allows you to remove roles. Don't do this if you are not sure what you are doing; it might severely cripple the permissions granted to users.")

            ->addArgument('name', InputArgument::REQUIRED, "The name of the role to be removed.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $role = $this->getContainer()->get('doctrine')
            ->getRepository('GalvesbandTraUserBundle:Role')
            ->findByName($input->getArgument('username'));

        if (is_null($role)) {
            $output->writeln("Role {$input->getArgument('name')} not found.");
        } else {
            try {
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->remove($role);
                $em->flush();

                $output->writeln("Role {$role->getName()} removed.");
            } catch (Exception $e) {
                $output->writeln("Error while removing role: ".$e->getMessage());
            }
        }
    }
}