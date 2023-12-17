<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateTablesNewColumnCategoria extends AbstractMigration
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
        $contas = $this->table('contas');
        $contas->addColumn('categoria', 'integer', ['null' => true])
            ->save();

        $contasHistorico = $this->table('contasHistorico');
        $contasHistorico->addColumn('categoria', 'integer', ['null' => true])
            ->save();


        $creditos = $this->table('creditos');
        $creditos->addColumn('categoria', 'integer', ['null' => true])
            ->save();

        $creditosHistorico = $this->table('creditosHistorico');
        $creditosHistorico->addColumn('categoria', 'integer', ['null' => true])
            ->save();
    }
}
