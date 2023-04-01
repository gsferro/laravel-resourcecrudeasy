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
    private function generateModel(string $entite): void
    {
        $path = 'app\Models\\' . $entite . '.php';
        $stub = 'models/';
        $stub .= $this->entites[ $entite ][ 'useFactory' ] ? 'model_factory' : 'model';

        if ($this->entites[$entite]['useFactory'] ) {
            $stub .= '_datatable';
        }
        
        $this->generate($entite, $path, $stub, 'Model');
    }
    
    private function generateDatatable(string $entite): void
    {
        if (!$this->entites[$entite]['useDatatable'] ) {
            return;
        }
        
        $path = 'app\Datatables\\' . $entite . 'Datatable.php';
        $this->generate($entite, $path, 'datatables', 'Model');
    }

    private function generateFactory(string $entite): void
    {
        if (!$this->entites[$entite]['useFactory'] ) {
            return;
        }

        $path = 'database\factories\\' . $entite . 'Factory.php';
        $this->generate($entite, $path, 'factory', 'Factory');
    }

    private function generateSeeder(string $entite): void
    {
        if (!$this->entites[$entite]['useSeeder'] ) {
            return;
        }

        $path = 'database\seeders\\' . $entite . 'Seeder.php';
        $this->generate($entite, $path, 'seeder', 'Seeder');
    }

    private function generateMigrate(string $entite): void
    {
        if (!$this->entites[$entite]['useMigrate']) {
            return;
        }

        // nome da table
        $arquivo = 'create_'.$this->entites[$entite]['str']->snake() . '_table.php';
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
        $stub = $this->entites[$entite]['useSeeder'] ? 'migrate_seeder' : 'migrate';
        $this->generate($entite, $path, $stub, 'Migration');
    }

    /*
    |---------------------------------------------------
    | Criar Pest Test
    |---------------------------------------------------
    |
    | Unit from Models
    |
    */
    private function generatePestUnitModel(string $entite): void
    {
        $path = 'tests\Unit\\' . $entite . '\Model\\' . $entite . 'Test.php';
        $this->generate($entite, $path, 'tests/unit/model', 'PestTest Unit Models');
    }

    private function generatePestUnitFactory(string $entite): void
    {
        if (!$this->entites[$entite]['useFactory'] ) {
            return;
        }

        $path = 'tests\Unit\\' . $entite . '\Factory\\' . $entite . 'FactoryTest.php';
        $this->generate($entite, $path, 'tests/unit/factory', 'PestTest Unit Factory');
    }

    private function generatePestUnitSeeder(string $entite): void
    {
        if (!$this->entites[$entite]['useSeeder'] ) {
            return;
        }

        $path = 'tests\Unit\\' . $entite . '\Seeder\\' . $entite . 'SeederTest.php';
        $this->generate($entite, $path, 'tests/unit/seeder', 'PestTest Unit Seeder');
    }
}