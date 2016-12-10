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
use \Galvesband\TraUserBundle\Entity\Group;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class AddGroupCommand
 *
 * Adds a new group to the system.
 *
 * @package Galvesband\TraUserBundle\Command
 */
class AddGroupCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('galvesband:tra-user:add-group')
            ->setDescription('Adds a new group.')
            ->setHelp("This command allows you to create groups.")

            ->addArgument('name', InputArgument::REQUIRED, "The name of the new group.")
            ->addOption('description', 'd', InputOption::VALUE_OPTIONAL, "A few words describing group's objective.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $group = new Group();
        $group->setName($input->getArgument('name'));
        if ($input->hasOption('description'))
            $group->setDescription($input->getOption('description'));

        $validator = $this->getContainer()->get('validator');
        $errors = $validator->validate($group);
        if (count($errors) === 0) {
            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->persist($group);
            $em->flush();

            $output->writeln('Group created: ' . $group->getName());
        } else {
            /** @var ConstraintViolation $error */
            foreach ($errors as $error) {
                $output->writeln("Validation error ({$error->getPropertyPath()}): {$error->getMessage()}");
            }
            $output->writeln("User was not created.");
        }
    }
}
