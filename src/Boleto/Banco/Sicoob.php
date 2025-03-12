<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Sicoob extends AbstractBoleto implements BoletoContract
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
    }

    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'Pagável preferencialmente nas cooperativas de crédito do Sicoob';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_SICOOB;

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['1', '2', '3'];

    /**
     * Se possui registro o boleto
     *
     * @var bool
     */
    protected $registro = true;

    /**
     * Código do cooperado no banco.
     *
     * @var int
     */
    protected $codigoCooperado;

    /**
     * Define se possui ou não registro
     *
     * @param bool $registro
     * @return Sicoob
     */
    public function setComRegistro($registro)
    {
        $this->registro = $registro;
        return $this;
    }

    /**
     * Retorna se é com registro.
     *
     * @return bool
     */
    public function isComRegistro()
    {
        return $this->registro;
    }

    /**
     * Define o código do cooperado
     *
     * @param int $codigoCooperado
     * @return Sicoob
     */
    public function setCodigoCooperado($codigoCooperado)
    {
        $this->codigoCooperado = $codigoCooperado;
        return $this;
    }

    /**
     * Retorna o código do cooperado
     *
     * @return int
     */
    public function getCodigoCooperado()
    {
        return $this->codigoCooperado;
    }

    /**
     * Retorna o campo Agência/Cooperado do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoCooperado()
    {
        return sprintf('%04s/%05s', $this->getAgencia(), $this->getCodigoCooperado());
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $numeroBoleto = Util::numberFormatGeral($this->getNumero(), 7);
        return $numeroBoleto . CalculoDV::modulo11($numeroBoleto);
    }

    /**
     * Método que retorna o nosso número usado no boleto.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return Util::maskString($this->getNossoNumero(), '######-#');
    }

    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     * @throws ValidationException
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }

        $campoLivre = Util::numberFormatGeral($this->getAgencia(), 4);
        $campoLivre .= Util::numberFormatGeral($this->getCodigoCooperado(), 5);
        $campoLivre .= Util::numberFormatGeral($this->getNossoNumero(), 7);
        $campoLivre .= '1';
        $campoLivre .= Util::modulo11($campoLivre);

        return $this->campoLivre = $campoLivre;
    }

    /**
     * Método para interpretar o campo livre
     *
     * @param $campoLivre
     * @return array
     */
    public static function parseCampoLivre($campoLivre)
    {
        return [
            'agencia'        => substr($campoLivre, 0, 4),
            'codigoCooperado' => substr($campoLivre, 4, 5),
            'nossoNumero'     => substr($campoLivre, 9, 7),
            'nossoNumeroDv'   => substr($campoLivre, 16, 1),
            'nossoNumeroFull' => substr($campoLivre, 9, 8),
        ];
    }
}
