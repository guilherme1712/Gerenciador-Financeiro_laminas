<?php

use Phinx\Migration\AbstractMigration;

class AlterColumnTypeDecimalToFloat extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('contas');
        $table->changeColumn('valor', 'float')
              ->update();


        $table = $this->table('contasHistorico');
        $table->changeColumn('valor', 'float')
            ->update();

        $table = $this->table('creditos');
        $table->changeColumn('valor', 'float')
              ->update();

        $table = $this->table('creditosHistorico');
        $table->changeColumn('valor', 'float')
              ->update();
    }
}
