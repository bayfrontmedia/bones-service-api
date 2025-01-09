<?php

namespace Bayfront\BonesService\Api\Commands;

use Bayfront\Bones\Exceptions\ServiceException;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Rbac\Models\PermissionsModel;
use Bayfront\BonesService\Rbac\Models\UsersModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\StringHelpers\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ApiSeed extends Command
{

    private RbacService $rbacService;

    /**
     * The container will resolve any dependencies.
     */
    public function __construct(ApiService $apiService)
    {
        $this->rbacService = $apiService->rbacService;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {

        $this->setName('api:seed')
            ->setDescription('Seed the API service tables')
            ->addArgument('email', InputArgument::REQUIRED, 'Admin user email address')
            ->addArgument('password', InputArgument::OPTIONAL, 'Admin user password')
            ->addOption('force', null, InputOption::VALUE_NONE);

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        if (!$input->getOption('force')) {

            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('This action will update the database. Are you sure you wish to continue with this action? [y/n]', false);

            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            if (!$helper->ask($input, $output, $question)) {

                $output->writeln('<info>API service seeding aborted!</info>');
                return Command::SUCCESS;
            }

        }

        $output->writeln('Seeding the API service tables...');

        try {

            $permissions = new PermissionsModel($this->rbacService);
            $users = new UsersModel($this->rbacService);

            if ($users->getCount() > 0) {

                $output->writeln('<info>Users already exist. API service seeding aborted!</info>');
                return Command::SUCCESS;

            }

            if ($input->getArgument('password')) {
                $password = $input->getArgument('password');
            } else {
                $password = Str::random(24);
            }

            $email = $input->getArgument('email');

            $user = $users->create([
                'email' => $email,
                'password' => $password,
                'enabled' => true,
                'admin' => true
            ]);

            $users->verify($email);

            $permissions->create([
                'name' => 'tenant:update',
                'description' => 'Update tenant'
            ]);

            $permissions->create([
                'name' => 'tenant_users:update',
                'description' => 'Update tenant users'
            ]);

            $permissions->create([
                'name' => 'tenant_users:delete',
                'description' => 'Delete tenant users'
            ]);

            $permissions->create([
                'name' => 'tenant_user_meta:create',
                'description' => 'Read tenant user meta'
            ]);

            $permissions->create([
                'name' => 'tenant_user_meta:read',
                'description' => 'Read tenant user meta'
            ]);

            $permissions->create([
                'name' => 'tenant_user_meta:update',
                'description' => 'Update tenant user meta'
            ]);

            $permissions->create([
                'name' => 'tenant_user_meta:delete',
                'description' => 'Delete tenant user meta'
            ]);

            $permissions->create([
                'name' => 'tenant_teams:create',
                'description' => 'Create teams'
            ]);

            $permissions->create([
                'name' => 'tenant_teams:read',
                'description' => 'Read teams'
            ]);

            $permissions->create([
                'name' => 'tenant_teams:update',
                'description' => 'Update teams'
            ]);

            $permissions->create([
                'name' => 'tenant_teams:delete',
                'description' => 'Delete teams'
            ]);

            $permissions->create([
                'name' => 'tenant_roles:create',
                'description' => 'Create roles'
            ]);

            $permissions->create([
                'name' => 'tenant_roles:read',
                'description' => 'Read roles'
            ]);

            $permissions->create([
                'name' => 'tenant_roles:update',
                'description' => 'Update roles'
            ]);

            $permissions->create([
                'name' => 'tenant_roles:delete',
                'description' => 'Delete roles'
            ]);

            $permissions->create([
                'name' => 'tenant_permissions:read',
                'description' => 'Delete roles'
            ]);

            $permissions->create([
                'name' => 'tenant_meta:create',
                'description' => 'Create tenant meta'
            ]);

            $permissions->create([
                'name' => 'tenant_meta:read',
                'description' => 'Read tenant meta'
            ]);

            $permissions->create([
                'name' => 'tenant_meta:update',
                'description' => 'Update tenant meta'
            ]);

            $permissions->create([
                'name' => 'tenant_meta:delete',
                'description' => 'Delete tenant meta'
            ]);

            $permissions->create([
                'name' => 'tenant_invitations:create',
                'description' => 'Create invitations'
            ]);

            $permissions->create([
                'name' => 'tenant_invitations:read',
                'description' => 'Read invitations'
            ]);

            $permissions->create([
                'name' => 'tenant_invitations:delete',
                'description' => 'Delete invitations'
            ]);

        } catch (ServiceException $e) {

            $output->writeLn('<error>Error seeding database:</error>');
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Command::FAILURE;

        }

        $output->writeLn('<info>User credentials:</info>');

        $table = new Table($output);

        $table
            ->setHeaders(['ID', 'Email', 'Password'])
            ->setRows([
                [$user->getPrimaryKey(), $user->get('email'), $password]
            ]);

        $table->render();

        $output->writeLn('<info>Seeding complete!</info>');

        return Command::SUCCESS;

    }

}