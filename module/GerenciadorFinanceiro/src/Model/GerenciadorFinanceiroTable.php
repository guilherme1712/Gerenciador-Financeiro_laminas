<?php

// module/GerenciadorFinanceiro/src/Model/GerenciadorFinanceiroTable.php

namespace GerenciadorFinanceiro\Model;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;

class GerenciadorFinanceiroTable
{
    protected $gerenciadorFinanceirotable;
    protected $adapter;
    private $sql;

    public function __construct(AdapterInterface $adapter = null)
    {
        $this->adapter = $adapter;
        $this->gerenciadorFinanceirotable = new TableGateway('gerenciadorFinanceiro', $this->adapter);
        $this->sql = new Sql($this->adapter);
    }

    public function searchBillings(): array
    {
        // Obtém o primeiro dia do mês atual
        $firstDayOfMonth = date('Y-m-01');
        // Obtém o último dia do mês atual
        $lastDayOfMonth = date('Y-m-t');
        $select = $this->sql->select(['c' => 'contas']);
        $select->where->greaterThanOrEqualTo(new Expression('DATE(data_termino_recorrente)'), $firstDayOfMonth)
                    ->and
                    ->lessThanOrEqualTo(new Expression('DATE(data_termino_recorrente)'), $lastDayOfMonth);
        $select->join(['b' => 'bancos'], 'c.banco = b.id_banco', ['nome'], Select::JOIN_LEFT);
        $select->order('c.data_termino_recorrente');

        $selectString = $this->sql->buildSqlString($select);
        $results = $this->adapter->query($selectString, $this->adapter::QUERY_MODE_EXECUTE)->toArray();

        return $results;
    }

    public function saveConta(array $conta)
    {
        $data = [
            'data' => $conta['data'],
            'descricao' => $conta['descricao'],
            'valor' => $conta['valor'],
            'recorrente' => $conta['recorrente'],
            'data_termino_recorrente' =>  $conta['data_termino_recorrente'],
            'status' => $conta['status'],
            'banco' => $conta['banco'],
            'categoria' => $conta['categoria'],
        ];

        $id = (int) $conta['id'];

        if ($id === 0) {
            $insert = $this->sql->insert('contas')->values($data);
            $insertString = $this->sql->buildSqlString($insert);

            $this->adapter->query($insertString, $this->adapter::QUERY_MODE_EXECUTE);

            // Adiciona ao histórico apenas quando uma nova conta é adicionada
            $conta['id'] = $this->gerenciadorFinanceirotable->getLastInsertValue();
            return $this->saveContaHistorico($conta);

        } else {
            return $this->updateConta($conta);
        }
    }

    private function updateConta(array $conta)
    {
        $data = [
            'data' => $conta['data'],
            'descricao' => $conta['descricao'],
            'valor' => $conta['valor'],
            'recorrente' => $conta['recorrente'],
            'data_termino_recorrente' =>  $conta['data_termino_recorrente'],
            'status' => $conta['status'],
            'banco' => $conta['banco'],
            'categoria' => $conta['categoria'],
        ];

        $id = (int) $conta['id'];
        $update = $this->sql->update('contas')->set($data)->where(['id' => $id]);
        $updateString = $this->sql->buildSqlString($update);

        if ($this->getConta($id)) {
            $this->adapter->query($updateString, $this->adapter::QUERY_MODE_EXECUTE);

            // Adiciona ao histórico apenas quando uma conta é editada
            return $this->saveContaHistorico($conta);
        } else {
            throw new \Exception('Conta não encontrada.');
        }
    }

    public function getConta(int $id)
    {
        $select = $this->sql->select('contas');
        $select->where(['id' => $id]);
        $selectString = $this->sql->buildSqlString($select);

        $rowset = $this->adapter->query($selectString, $this->adapter::QUERY_MODE_EXECUTE);
        $row = $rowset->current();

        if (!$row) {
            throw new \Exception("Conta não encontrada: $id");
        }

        return $row;
    }

    public function deleteConta(int $id)
    {
        $delete = $this->sql->delete('contas')->where(['id' => $id]);
        $deleteString = $this->sql->buildSqlString($delete);

        return $this->adapter->query($deleteString, $this->adapter::QUERY_MODE_EXECUTE);
    }

    public function saveContaHistorico(array $conta)
    {
        $data = [
            'data' => $conta['data'],
            'descricao' => $conta['descricao'],
            'valor' => $conta['valor'],
            'recorrente' => $conta['recorrente'],
            'data_termino_recorrente' =>  $conta['data_termino_recorrente'],
            'status' => $conta['status'],
            'banco' => $conta['banco'],
            'categoria' => $conta['categoria'],
        ];

        $insert = $this->sql->insert('contasHistorico')->values($data);
        $insertString = $this->sql->buildSqlString($insert);

        return $this->adapter->query($insertString, $this->adapter::QUERY_MODE_EXECUTE);
    }

    public function searchCreditos(): array
    {
        // Obtém o primeiro dia do mês atual
        $firstDayOfMonth = date('Y-m-01');
        // Obtém o último dia do mês atual
        $lastDayOfMonth = date('Y-m-t');

        $select = $this->sql->select(['c' => 'creditos']);
        // Adiciona uma condição para filtrar as contas do mês corrente
        $select->where->between(new Expression('DATE(data_termino_recorrente)'), $firstDayOfMonth, $lastDayOfMonth);
        $select->join(['b' => 'bancos'], 'c.banco = b.id_banco', ['nome'], Select::JOIN_LEFT);
        $select->order('c.data_termino_recorrente');

        $selectString = $this->sql->buildSqlString($select);
        return $this->adapter->query($selectString, $this->adapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function saveCredito(array $credito)
    {
        $data = [
            'data' => $credito['data'],
            'descricao' => $credito['descricao'],
            'valor' => $credito['valor'],
            'recorrente' => $credito['recorrente'],
            'data_termino_recorrente' => $credito['data_termino_recorrente'],
            'status' => $credito['status'],
            'banco' => $credito['banco'],
            'categoria' => $credito['categoria'],
        ];

        $id = (int) $credito['id'];

        if ($id === 0) {
            $insert = $this->sql->insert('creditos')->values($data);
            $insertString = $this->sql->buildSqlString($insert);

            $this->adapter->query($insertString, $this->adapter::QUERY_MODE_EXECUTE);

            // Adiciona ao histórico apenas quando um novo crédito é adicionado
            $credito['id'] = $this->gerenciadorFinanceirotable->getLastInsertValue();
            return $this->saveCreditoHistorico($credito);

        } else {
            $this->updateCredito($credito);
        }
    }

    private function updateCredito(array $credito)
    {
        $data = [
            'data' => $credito['data'],
            'descricao' => $credito['descricao'],
            'valor' => $credito['valor'],
            'recorrente' => $credito['recorrente'],
            'data_termino_recorrente' => $credito['data_termino_recorrente'],
            'status' => $credito['status'],
            'banco' => $credito['banco'],
            'categoria' => $credito['categoria'],
        ];

        $id = (int) $credito['id'];
        $update = $this->sql->update('creditos')->set($data)->where(['id' => $id]);
        $updateString = $this->sql->buildSqlString($update);

        if ($this->getCredito($id)) {
            $this->adapter->query($updateString, $this->adapter::QUERY_MODE_EXECUTE);

            // Adiciona ao histórico apenas quando um crédito é editado
            return $this->saveCreditoHistorico($credito);
        } else {
            throw new \Exception('Crédito não encontrado.');
        }
    }

    public function getCredito(int $id)
    {
        $select = $this->sql->select('creditos');
        $select->where(['id' => $id]);
        $selectString = $this->sql->buildSqlString($select);

        $rowset = $this->adapter->query($selectString, $this->adapter::QUERY_MODE_EXECUTE);
        $row = $rowset->current();

        if (!$row) {
            throw new \Exception("Crédito não encontrado: $id");
        }

        return $row;
    }

    public function deleteCredito(int $id)
    {
        // Obtém os dados do crédito antes de excluí-lo
        $credito = $this->getCredito($id);

        $delete = $this->sql->delete('creditos')->where(['id' => $id]);
        $deleteString = $this->sql->buildSqlString($delete);

        $this->adapter->query($deleteString, $this->adapter::QUERY_MODE_EXECUTE);
    }

    public function saveCreditoHistorico(array $credito)
    {
        $data = [
            'data' => $credito['data'],
            'descricao' => $credito['descricao'],
            'valor' => $credito['valor'],
            'recorrente' => $credito['recorrente'],
            'data_termino_recorrente' => $credito['data_termino_recorrente'],
            'status' => $credito['status'],
            'banco' => $credito['banco'],
            'categoria' => $credito['categoria'],
        ];

        $insert = $this->sql->insert('creditosHistorico')->values($data);
        $insertString = $this->sql->buildSqlString($insert);

        $this->adapter->query($insertString, $this->adapter::QUERY_MODE_EXECUTE);
    }

    public function getListadoContas(array $formData): array
    {
        $select = $this->sql->select("contasHistorico");
        if  ($formData['dataFim']) {
            $select->where->between('data_termino_recorrente', $formData['dataDesde'], $formData['dataFim']);
        } else {
            $select->where->equalTo('data_termino_recorrente', $formData['dataDesde']);
        }
        $select->order('data_termino_recorrente');

        $selectString = $this->sql->buildSqlString($select);
        return $this->adapter->query($selectString, $this->adapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function getListadoCredito(array $formData): array
    {
        $select = $this->sql->select("creditosHistorico");
        if  ($formData['dataFim']) {
            $select->where->between(new Expression('DATE(data_termino_recorrente)'), $formData['dataDesde'], $formData['dataFim']);
        } else {
            $select->where->equalTo('data_termino_recorrente', $formData['dataDesde']);
        }
        $select->order('data_termino_recorrente');

        $selectString = $this->sql->buildSqlString($select);
        return $this->adapter->query($selectString, $this->adapter::QUERY_MODE_EXECUTE)->toArray();
    }

    public function saveTotalMes($totalMes)
    {
        $today = new \DateTime();
        $data = [
            'mes_referencia' => $today->format('Y-m'),
            'total_mes' => $totalMes,
            'created_at' => $today->format('Y-m-d'),
        ];

        $insert = $this->sql->insert('totalMes')->values($data);
        $insertString = $this->sql->buildSqlString($insert);
        return $this->adapter->query($insertString, $this->adapter::QUERY_MODE_EXECUTE);
    }

    public function searchTotalMes($mesReferencia)
    {
        $select = $this->sql->select("totalMes");
        $select->where(['mes_referencia' => $mesReferencia]);

        $selectString = $this->sql->buildSqlString($select);
        $result = $this->adapter->query($selectString, $this->adapter::QUERY_MODE_EXECUTE);

        // Assuming you want to return the 'total_mes' column
        $totalMes = $result->current()['total_mes'] ?? null;

        return $totalMes;
    }

    public function getBancos(): array
    {
        $select = $this->sql->select('bancos');

        $selectString = $this->sql->buildSqlString($select);
        return $this->adapter->query($selectString, $this->adapter::QUERY_MODE_EXECUTE)->toArray();
    }
}
