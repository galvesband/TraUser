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
use \Galvesband\TraUserBundle\Entity\Role;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class AddRoleCommand
 *
 * Adds a new role to the system.
 *
 * @package Galvesband\TraUserBundle\Command
 */
class AddRoleCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('galvesband:tra-user:add-role')
            ->setDescription('Adds a new role.')
            ->setHelp("This command allows you to create roles. Roles are internal permission bundles used by the application. Don't add or remove roles if you don't known what you are doing.")

            ->addArgument('name', InputArgument::REQUIRED, "The name of the new role.")
            ->addArgument('role', InputArgument::REQUIRED, "The role internal identifier.")
            ->addOption("description", "d", InputOption::VALUE_OPTIONAL, "A few words describing the role permissions.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $role = new Role();
        $role->setName($input->getArgument('name'));
        $role->setRole($input->getArgument('role'));
        if ($input->hasOption('description'))
            $role->setDescription($input->getOption('description'));

        $validator = $this->getContainer()->get('validator');
        $errors = $validator->validate($role);
        if (count($errors) === 0) {
            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->persist($role);
            $em->flush();

            $output->writeln('Role created: ' . $role->getName());
        } else {
            /** @var ConstraintViolation $error */
            foreach ($errors as $error) {
                $output->writeln("Validation error ({$error->getPropertyPath()}): {$error->getMessage()}");
            }
            $output->writeln("Role was not created.");
        }
    }
}
