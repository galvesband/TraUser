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
use \Galvesband\TraUserBundle\Entity\Group;

/**
 * Class RemoveGroupCommand
 *
 * Removes a group from the system.
 *
 * @package Galvesband\TraUserBundle\Command
 */
class RemoveGroupCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('galvesband:tra-user:remove-group')
            ->setDescription('Removes a group.')
            ->setHelp("This command allows you to remove groups.")

            ->addArgument('name', InputArgument::REQUIRED, "The name of the group to be removed.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $group = $this->getContainer()->get('doctrine')
            ->getRepository('GalvesbandTraUserBundle:Group')
            ->findByName($input->getArgument('name'));

        if (is_null($group)) {
            $output->writeln("Group {$input->getArgument('name')} not found.");
        } else {
            try {
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->remove($group);
                $em->flush();

                $output->writeln("Group {$group->getName()} removed.");
            } catch (Exception $e) {
                $output->writeln("Error while removing group: ".$e->getMessage());
            }
        }
    }
}