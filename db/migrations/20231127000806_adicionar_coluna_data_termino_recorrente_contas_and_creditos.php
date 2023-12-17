<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdicionarColunaDataTerminoRecorrenteContasAndCreditos extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('contas');
        $table->addColumn('data_termino_recorrente', 'date', ['null' => true, 'after' => 'recorrente'])
              ->update();

        $table = $this->table('creditos');
        $table->addColumn('data_termino_recorrente', 'date', ['null' => true, 'after' => 'recorrente'])
            ->update();

        $table = $this->table('contasHistorico');
        $table->addColumn('data_termino_recorrente', 'date', ['null' => true, 'after' => 'recorrente'])
                ->update();

        $table = $this->table('creditosHistorico');
        $table->addColumn('data_termino_recorrente', 'date', ['null' => true, 'after' => 'recorrente'])
            ->update();
    }
}
