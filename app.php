<?php

//classe dashboard
class Dashboard {

    public $data_inicio;
    public $data_fim;
    public $numeroVendas;
    public $totalVendas;
    public $clientesAtivos;
    public $clientesInativos;
    public $totalSugestoes;
    public $totalCriticas;
    public $totalElogios;
    public $totalDespesas;

    public function __get($atributo){
        return $this->$atributo;
    }

    public function __set($atributo, $valor){
        $this->$atributo = $valor;
        return $this;
    }

}

//classe de conexão bd
class Conexao {
    private $host = 'localhost';
    private $dbname = 'dashboard';
    private $user = 'root';
    private $pass = '';

    public function conectar(){
        try{

            $conexao = new PDO(
                "mysql:host=$this->host;dbname=$this->dbname",
                "$this->user",
                "$this->pass"

            );

            //
            $conexao->exec('set names utf8');

            return $conexao;

        }catch (PDOException $e) {
            echo '<p>'. $e->getMessage() . '</p>';
        }
    }
}

//classe (model)

class Bd {
    private $conexão;
    private $dashboard;

    public function __construct(Conexao $conexao, Dashboard $dashboard){
        $this->conexao = $conexao->conectar();
        $this->dashboard = $dashboard;
    } 

    public function getNumeroVendas(){ //numero de vendas
        $query = 'SELECT 
                    COUNT(*) AS numero_vendas
                  FROM
                    tb_vendas
                  WHERE 
                    data_venda BETWEEN :data_inicio AND :data_fim';

        $stmt = $this->conexao->prepare($query);
        $stmt->bindValue(':data_inicio', $this->dashboard->__get('data_inicio'));
        $stmt->bindValue(':data_fim', $this->dashboard->__get('data_fim'));
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->numero_vendas;
    }


    public function getTotalVendas(){//total vendas
        $query = 'SELECT 
                    SUM(total) AS total_vendas
                  FROM
                    tb_vendas
                  WHERE 
                    data_venda BETWEEN :data_inicio AND :data_fim';

        $stmt = $this->conexao->prepare($query);
        $stmt->bindValue(':data_inicio', $this->dashboard->__get('data_inicio'));
        $stmt->bindValue(':data_fim', $this->dashboard->__get('data_fim'));
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->total_vendas;
    }

    public function getClientesAtivos() {//Clientes Ativo/Inativos
        $query = 'SELECT
                   COUNT(CASE WHEN cliente_ativo = 1 THEN 1 END) AS clientes_ativos,
                   COUNT(CASE WHEN cliente_ativo = 0 THEN 0 END) AS clientes_inativos
                  FROM
                    tb_clientes
                  WHERE cliente_ativo = :clientesAtivos OR cliente_ativo = :clientesInativos';
        $stmt = $this->conexao->prepare($query);
        $stmt->bindValue(':clientesAtivos', $this->dashboard->__get('clientesAtivos'));
        $stmt->bindValue(':clientesInativos', $this->dashboard->__get('clientesInativos'));
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

 
    // -- 1 = crítica | 2 = sugestão | 3 = elogio

    public function getTotalContatos() {// Total de contatos
        $query = 'SELECT 
                    COUNT(CASE WHEN tipo_contato = 1 THEN 1 END) AS total_sugestoes,
                    COUNT(CASE WHEN tipo_contato = 2 THEN 2 END) AS total_criticas,
                    COUNT(CASE WHEN tipo_contato = 3 THEN 3 END) AS total_elogios
                  FROM 
                    tb_contatos
                  WHERE 
                    tipo_contato = :totalSugestoes OR tipo_contato = :totalCriticas OR tipo_contato = :totalElogios';

        $stmt = $this->conexao->prepare($query);
        $stmt->bindValue(':totalSugestoes', $this->dashboard->__get('totalSugestoes'));
        $stmt->bindValue(':totalCriticas', $this->dashboard->__get('totalCriticas'));
        $stmt->bindValue(':totalElogios', $this->dashboard->__get('totalElogios'));
        $stmt->execute();
        
        
        
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getTotalDespesas(){//Total despesas
        $query = 'SELECT 
                    SUM(total) AS total_despesas
                  FROM
                    tb_despesas
                  WHERE data_despesa BETWEEN :data_inicio AND :data_fim';

        $stmt = $this->conexao->prepare($query);
        $stmt->bindValue(':data_inicio', $this->dashboard->__get('data_inicio'));
        $stmt->bindValue(':data_fim', $this->dashboard->__get('data_fim'));
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->total_despesas;
    }

}

//lógica do script
$dashboard = new Dashboard();
$conexao = new Conexao();

$competencia = explode('-', $_GET['competencia']);
$ano = $competencia[0];
$mes = $competencia[1];

$dias_do_mes = date('t', strtotime("$ano-$mes-01"));

//Passando os valores como parametro 
$dashboard->__set('data_inicio', $ano.'-'.$mes.'-01');
$dashboard->__set('data_fim', $ano.'-'.$mes.'-'.$dias_do_mes);
$dashboard->__set('clientesAtivos', '1');
$dashboard->__set('clientesInativos', '0');
$dashboard->__set('totalSugestoes', '1');
$dashboard->__set('totalCriticas', '2');
$dashboard->__set('totalElogios', '3');

$bd = new Bd($conexao, $dashboard);

//atualizando os atributos como parametro os métodos como função

$dashboard->__set('numeroVendas', $bd->getNumeroVendas());
$dashboard->__set('totalVendas', $bd->getTotalVendas());
$dashboard->__set('clientesAtivos', $bd->getClientesAtivos()->clientes_ativos);
$dashboard->__set('clientesInativos', $bd->getClientesAtivos()->clientes_inativos);
$dashboard->__set('totalSugestoes', $bd->getTotalContatos()->total_sugestoes);
$dashboard->__set('totalCriticas', $bd->getTotalContatos()->total_criticas);
$dashboard->__set('totalElogios', $bd->getTotalContatos()->total_elogios);
$dashboard->__set('totalDespesas', $bd->getTotalDespesas());

// print_r($dashboard->__get('totalDespesas'));

echo json_encode($dashboard);



?>