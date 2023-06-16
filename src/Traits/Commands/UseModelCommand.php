<?php

namespace Gsferro\ResourceCrudEasy\Traits\Commands;

trait UseModelCommand
{
    /*
    |---------------------------------------------------
    | Criar Models
    |---------------------------------------------------
    |
    | Datatables
    | Factory
    | Seeder
    | Migration
    | Police (?)
    |
    */
    private function generateModel(string $entity): void
    {
        $path = 'app\Models\\' . $entity . '.php';
        $stub = 'models/';

        $stub .= $this->entitys[ $entity ][ 'isAuxModel' ] ? 'auxiliary/' : '';
        $stub .= $this->entitys[ $entity ][ 'useFactory' ] ? 'model_factory' : 'model';

        if ($this->entitys[$entity]['useDatatable'] ) {
            $stub .= '_datatable';
        }
        
        $this->generate($entity, $path, $stub, 'Model');
    }
    
    private function generateDatatable(string $entity): void
    {
        if (!$this->entitys[$entity]['useDatatable'] ) {
            return;
        }
        
        $path = 'app\Datatables\\' . $entity . 'Datatable.php';
        $this->generate($entity, $path, 'datatables', 'Datatable');
    }

    private function generateFactory(string $entity): void
    {
        if (!$this->entitys[$entity]['useFactory'] ) {
            return;
        }

        $path = 'database\factories\\' . $entity . 'Factory.php';
        $this->generate($entity, $path, 'factory', 'Factory');
    }

    private function generateSeeder(string $entity): void
    {
        if (!$this->entitys[$entity]['useSeeder'] ) {
            return;
        }

        $path = 'database\seeders\\' . $entity . 'Seeder.php';
        $this->generate($entity, $path, 'seeder', 'Seeder');
    }

    private function generateMigrate(string $entity): void
    {
        if (!$this->entitys[$entity]['useMigrate']) {
            return;
        }

        // nome da table
        $arquivo = 'create_'.$this->entitys[$entity]['str']->snake() . '_table.php';
        // sempre fazer override
        $override = true;
        // caso exista, pega o nome
        $existsMigrate = null;
        // lista todos as migrates
        $migrations = dir(database_path('migrations'));
        // le toda a pastas
        while ($migration = $migrations->read()) {
            // verifica se a migrate é o arquivo que sera criado
            if (substr($migration, 18) == $arquivo) {

                // salva o nome para replace, em caso de override
                $existsMigrate = $migration;
                // pergunta ao usuário se deseja fazer override
                $override = $this->confirm('Already Exists Migrate. Want to replace?', false);
            }

            // caso marque como false, return
            if (!$override) {
                return;
            }
        }
        $migrations->close();
        // o nome sera ou o atual ou novo
        $migrateName = $existsMigrate ?? now()->format('Y_m_d_his') . '_' . $arquivo;
        $path        = 'database\migrations\\' . $migrateName;

        // caso tenha criado o seeder, coloca para executar ao rodar a migrate
        $stub  = $this->entitys[ $entity ][ 'useSeeder' ] ? 'migrate_seeder' : 'migrate';
        $stub .= $this->entitys[ $entity ][ 'isAuxModel' ] ? '_aux' : '';
        $this->generate($entity, $path, $stub, 'Migration');
    }

    /*
    |---------------------------------------------------
    | Criar Pest Test
    |---------------------------------------------------
    |
    | Unit from Models
    |
    */
    private function generatePestUnitModel(string $entity): void
    {
        $path = 'tests\Unit\\' . $entity . '\Model\\' . $entity . 'Test.php';
        $this->generate($entity, $path, 'tests/unit/model', 'PestTest Unit Models');
    }

    private function generatePestUnitFactory(string $entity): void
    {
        if (!$this->entitys[$entity]['useFactory'] ) {
            return;
        }

        $path = 'tests\Unit\\' . $entity . '\Factory\\' . $entity . 'FactoryTest.php';
        $this->generate($entity, $path, 'tests/unit/factory', 'PestTest Unit Factory');
    }

    private function generatePestUnitSeeder(string $entity): void
    {
        if (!$this->entitys[$entity]['useSeeder'] ) {
            return;
        }

        $path = 'tests\Unit\\' . $entity . '\Seeder\\' . $entity . 'SeederTest.php';
        $this->generate($entity, $path, 'tests/unit/seeder', 'PestTest Unit Seeder');
    }
}