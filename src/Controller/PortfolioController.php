<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\ORM\TableRegistry;

/**
 * Portfolio Controller
 *
 */
class PortfolioController extends AppController
{

    private $tolerancia = 1e-5;

    function fatorial($n)
    {
        $prod = 1;
        for ($i = 1; $i <= $n; $i++) {
            $prod *= $i;
        }
        return $prod;
    }

    function equals($x, $y)
    {
        $dif = abs((float) $x - (float) $y) < $this->tolerancia;
        return $dif;
    }

    function meu_array_search($needle, array $haystack, $key)
    {
        //echo("Procurando:" . $needle);
        for ($i = 0; $i < count($haystack); $i++) {
            if (abs($haystack[$i][$key] - $needle) < $this->tolerancia) {
                //echo(" achou na posição " . $i . "<br/>");
                return $i;
            }
        }
        //echo(" não achou<br/>");
        return -1;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $RetornoRiscoFundos = TableRegistry::getTableLocator()->get('RetornoRiscoFundos');
        $this->paginate = [
            'contain' => ['CnpjFundos' => ['CadastroFundos' => ['TipoClasseFundos']]],
        ];
        $retornoRiscoFundos = $this->paginate($RetornoRiscoFundos);
        $this->set(compact('retornoRiscoFundos'));

        foreach ($RetornoRiscoFundos->find() as $fundo) {
            $rentabFundos[] = $fundo->rentab_media;
            $desviosFundos[] = $fundo->desvio_padrao;
            $idFundos[] = $fundo->cnpj_fundo_id;
        }
        for ($i = 0; $i < count($desviosFundos); $i++) {
            for ($j = 0; $j < count($desviosFundos); $j++) {
                $covar[$i][$j] = $desviosFundos[$i] * $desviosFundos[$j];
            }
        }

        $countRec = $RetornoRiscoFundos->find()->count();
        switch ($countRec) {
            case 1:
            case 2:
                $compensador = 8;
                break;
            case 3:
                $compensador = 6;
                break;
            case 4:
                $compensador = 3;
                break;
            case 5:
                $compensador = 2;
                break;
            case 6:
                $compensador = 2;
                break;
            case 7:
                $compensador = 2;
                break;
            default:
                $compensador = 2;
                break;
        }
        //if ($countRec>4 && $countRec % 2 ==0) {
        //$compensador = 1.5;
        //}
        $p = 1.0 / $countRec / $compensador;
        $min = $p; //0.0
        $max = 1.0 - $p; //1.0;
        $pos = $countRec - 1;
        $pesos = array_fill(0, $pos, $min);
        $pesos[$pos] = 1 - $min * $pos;
        $iteracoes = 0;
        //echo("count:".$countRec.", p:".$p.", min:".$min.", max:".$max.", total:".array_sum($pesos).", p's:".($pesos[$pos]/$p)."<br/>");
        //
        $menorRiscoPorRent = array();
        $maiorRentPorRisco = array();
        $alocacoes = ['fundos_id' => $idFundos, 'alocacoes' => []];
        do {
            // possui um arranjo válido. Calcula resultados da carteira
            $rentabAlocacao = 0.0;
            $i = 0;
            foreach ($rentabFundos as $rentabFundo) {
                $rentabAlocacao += $rentabFundo * $pesos[$i++];
            }
            $riscoAlocacao = 0.0;
            for ($i = 0; $i < $countRec; $i++) {
                for ($j = 0; $j < $countRec; $j++) {
                    $riscoAlocacao += $pesos[$i] * $pesos[$j] * $covar[$i][$j];
                }
            }
            $idxMenorRisco = $this->meu_array_search(round($rentabAlocacao, 2), $menorRiscoPorRent, 'rentab');
            if ($idxMenorRisco == -1) {
                $menorRiscoPorRent[] = ['rentab' => round($rentabAlocacao, 2), 'risco' => round($riscoAlocacao, 2)];
                //echo('Rentab:' . round($rentabAlocacao, 2) . ', Incluído novo:' . round($riscoAlocacao, 2) . "<br/>");
            } else if (round($riscoAlocacao, 2) < $menorRiscoPorRent[$idxMenorRisco]['risco']) {
                //echo('Rentab:' . round($riscoAlocacao,2) . ', era:' . $menorRiscoPorRent[$idxMenorRisco]['risco'] . " -> ");
                $menorRiscoPorRent[$idxMenorRisco]['risco'] = round($riscoAlocacao, 2);
                //echo($menorRiscoPorRent[$idxMenorRisco]['risco'] . '<br/>');
            }

            $idxMaiorRent = $this->meu_array_search(round($riscoAlocacao, 2), $maiorRentPorRisco, 'risco');
            if ($idxMaiorRent == -1) {
                $maiorRentPorRisco[] = ['rentab' => round($rentabAlocacao, 2), 'risco' => round($riscoAlocacao, 2)];
                //echo('Risco:' . $riscoAlocacao . ', Incluído novo:' . $rentabAlocacao . "<br/>");
            } else if (round($rentabAlocacao, 2) > $maiorRentPorRisco[$idxMaiorRent]['rentab']) {
                //echo('Risco:' . $riscoAlocacao . ', era:' . $maiorRentPorRisco[$idxMaiorRent]['rentab'] . " -> ");
                $maiorRentPorRisco[$idxMaiorRent]['rentab'] = round($rentabAlocacao, 2);
                //echo($maiorRentPorRisco[$idxMaiorRent]['rentab'] . '<br/>');
            }

            $alocacoes['alocacoes'][] = ['id' => $iteracoes, 'pesos' => $pesos, 'rentabilidade' => $rentabAlocacao, 'risco' => $riscoAlocacao, 'numFundos' => $countRec, 'inFronteira' => 0];
            //
            // calcula próximo arranjo
            $iteracoes++;
            $pos = $countRec - 2;
            $done = false;
            while (!$done && $pos >= 0) {
                $sum = 0;
                for ($i = 0; $i < $pos; $i++) {
                    $sum += $pesos[$i];
                }
                $esteMax = $max - $sum - ($min * ($countRec - $pos - 2));
                //echo("pos:".$pos.", sum:".$sum.", pes[]:".$pesos[$pos].", comp:".$esteMax."<br/>");
                if ($pesos[$pos] >= $esteMax - $this->tolerancia) {
                    //if ($this->equals($pesos[$pos], $esteMax)) {
                    $pesos[$pos] = $min;
                    $pos--;
                } else {
                    $pesos[$pos] += $p;
                    $done = true;
                }
            }
            $sum = 0;
            for ($i = 0; $i <= $countRec - 2; $i++) {
                $sum += $pesos[$i];
            }
            $pesos[$countRec - 1] = 1 - $sum;
            //echo("peso total:".array_sum($pesos)." último peso:".$pesos[$coufalsentRec-1].", p's:".($pesos[$countRec-1]/$p)."<br/>");
        } while ($pos >= 0 && $iteracoes < 1e6);

        //echo('maiorRentPorRisco');
        //var_dump($maiorRentPorRisco);
        //echo('menorRiscoPorRent');
        //var_dump($menorRiscoPorRent);
        //exit();

        for ($i = 0; $i < count($alocacoes['alocacoes']); $i++) {
            $idxMaiorRent = $this->meu_array_search(round($alocacoes['alocacoes'][$i]['risco'], 2), $maiorRentPorRisco, 'risco');
            $maiorRent = $maiorRentPorRisco[$idxMaiorRent]['rentab'];
            $esteRent = round($alocacoes['alocacoes'][$i]['rentabilidade'], 2);
            //if (abs($maiorRent - $esteRent) < $this->tolerancia) {
            //	$alocacoes['alocacoes'][$i]['inFronteira'] ++;
            //}
            $idxMenorRisco = $this->meu_array_search(round($alocacoes['alocacoes'][$i]['rentabilidade'], 2), $menorRiscoPorRent, 'rentab');
            $menorRisco = $menorRiscoPorRent[$idxMenorRisco]['risco'];
            $esteRisco = round($alocacoes['alocacoes'][$i]['risco'], 2);
            if ((abs($menorRisco - $esteRisco) < $this->tolerancia) && (abs($maiorRent - $esteRent) < $this->tolerancia)) { // && (abs($maiorRent - $esteRent) < $this->tolerancia)) {
                $alocacoes['alocacoes'][$i]['inFronteira']++;
            }
        }

        $this->set(compact('alocacoes'));
    }



    /**
     * Fronteira method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function operacoes()
    {
    }


    /**
     * Analise method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function analise($id = 3)
    {
        $CarteirasInvestimentos = $this->getTableLocator()->get('CarteirasInvestimentos');
        $carteirasInvestimento = $CarteirasInvestimentos->get($id);

        $this->set(compact('carteirasInvestimento'));

        $OperacoesFinanceiras = $this->getTableLocator()->get('OperacoesFinanceiras');
        $operacoesFinanceiras = $OperacoesFinanceiras->find(
            'all',
            [
                'conditions' => ['carteiras_investimento_id' => $id],
                'contain' => ['CnpjFundos.CadastroFundos.TipoClasseFundos', 'TipoOperacoesFinanceiras'],
            ]
        );


        $alocacoesPorAtivo = [];
        $alocacoesPorClasse = [];
        $aplicacaoTotal = 0;
        $aplicacaoPorMes = [];
        $idsFundos = [];

        foreach ($operacoesFinanceiras as $operacoesFinanceira) {
            $nomeFundo = $operacoesFinanceira->cnpj_fundo['DENOM_SOCIAL'];

            $fundo = $operacoesFinanceira->cnpj_fundo;
            $idsFundos[] = $operacoesFinanceira->cnpj_fundo->id;

            if (!array_key_exists($nomeFundo, $alocacoesPorAtivo)) {
                $alocacoesPorAtivo[$nomeFundo] = 0;
            }

            $data = $operacoesFinanceira->data->getTimestamp();
            if (!array_key_exists($data, $aplicacaoPorMes)) {
                $aplicacaoPorMes[$data] = 0;
            }

            if ($operacoesFinanceira->tipo_operacoes_financeira->is_aplicacao) {
                $alocacoesPorAtivo[$nomeFundo] += $operacoesFinanceira->valor_total;
                $aplicacaoTotal += $operacoesFinanceira->valor_total;

                $aplicacaoPorMes[$data] += $operacoesFinanceira->valor_total;
            } else {
                $alocacoesPorAtivo[$nomeFundo] -= $operacoesFinanceira->valor_total;
                $aplicacaoTotal -= $operacoesFinanceira->valor_total;

                $aplicacaoPorMes[$data] -= $operacoesFinanceira->valor_total;
            }
        }

        ksort($aplicacaoPorMes);

        $this->set(compact('alocacoesPorAtivo', 'alocacoesPorClasse', 'aplicacaoPorMes'));
        $this->set(compact('aplicacaoTotal'));


        $IndicadoresCarteiras = $this->getTableLocator()->get('IndicadoresCarteiras');
        $indicadores = $IndicadoresCarteiras->find(
            'all',
            [
                'conditions' => [
                    'carteiras_investimento_id' => $id,
                ],
            ]
        );

        $this->set(compact('indicadores'));
    }


    /**
     * Fronteira method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function comparacao()
    {
    }



    /**
     * Fronteira method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function fronteira()
    {
        $RetornoRiscoFundos = TableRegistry::getTableLocator()->get('RetornoRiscoFundos');
        $this->paginate = [
            'contain' => ['CnpjFundos' => ['CadastroFundos' => ['TipoClasseFundos']]],
        ];
        $retornoRiscoFundos = $this->paginate($RetornoRiscoFundos);
        $this->set(compact('retornoRiscoFundos'));
        //var_dump($retornoRiscoFundos);
        //exit();

        foreach ($RetornoRiscoFundos->find() as $fundo) {
            $rentabFundos[] = $fundo->rentab_media;
            $desviosFundos[] = $fundo->desvio_padrao;
            $idFundos[] = $fundo->cnpj_fundo_id;
        }
        for ($i = 0; $i < count($desviosFundos); $i++) {
            for ($j = 0; $j < count($desviosFundos); $j++) {
                $covar[$i][$j] = $desviosFundos[$i] * $desviosFundos[$j];
            }
        }

        $countRec = $RetornoRiscoFundos->find()->count();
        switch ($countRec) {
            case 1:
            case 2:
                $compensador = 8;
                break;
            case 3:
                $compensador = 6;
                break;
            case 4:
                $compensador = 3;
                break;
            case 5:
                $compensador = 2;
                break;
            case 6:
                $compensador = 2;
                break;
            case 7:
                $compensador = 2;
                break;
            default:
                $compensador = 2;
                break;
        }
        //if ($countRec>4 && $countRec % 2 ==0) {
        //$compensador = 1.5;
        //}
        $p = 1.0 / $countRec / $compensador;
        $min = $p; //0.0
        $max = 1.0 - $p; //1.0;
        $pos = $countRec - 1;
        $pesos = array_fill(0, $pos, $min);
        $pesos[$pos] = 1 - $min * $pos;
        $iteracoes = 0;
        //echo("count:".$countRec.", p:".$p.", min:".$min.", max:".$max.", total:".array_sum($pesos).", p's:".($pesos[$pos]/$p)."<br/>");
        //
        $menorRiscoPorRent = array();
        $maiorRentPorRisco = array();
        $alocacoes = ['fundos_id' => $idFundos, 'alocacoes' => []];
        do {
            // possui um arranjo válido. Calcula resultados da carteira
            $rentabAlocacao = 0.0;
            $i = 0;
            foreach ($rentabFundos as $rentabFundo) {
                $rentabAlocacao += $rentabFundo * $pesos[$i++];
            }
            $riscoAlocacao = 0.0;
            for ($i = 0; $i < $countRec; $i++) {
                for ($j = 0; $j < $countRec; $j++) {
                    $riscoAlocacao += $pesos[$i] * $pesos[$j] * $covar[$i][$j];
                }
            }
            $idxMenorRisco = $this->meu_array_search(round($rentabAlocacao, 2), $menorRiscoPorRent, 'rentab');
            if ($idxMenorRisco == -1) {
                $menorRiscoPorRent[] = ['rentab' => round($rentabAlocacao, 2), 'risco' => round($riscoAlocacao, 2)];
                //echo('Rentab:' . round($rentabAlocacao, 2) . ', Incluído novo:' . round($riscoAlocacao, 2) . "<br/>");
            } else if (round($riscoAlocacao, 2) < $menorRiscoPorRent[$idxMenorRisco]['risco']) {
                //echo('Rentab:' . round($riscoAlocacao,2) . ', era:' . $menorRiscoPorRent[$idxMenorRisco]['risco'] . " -> ");
                $menorRiscoPorRent[$idxMenorRisco]['risco'] = round($riscoAlocacao, 2);
                //echo($menorRiscoPorRent[$idxMenorRisco]['risco'] . '<br/>');
            }

            $idxMaiorRent = $this->meu_array_search(round($riscoAlocacao, 2), $maiorRentPorRisco, 'risco');
            if ($idxMaiorRent == -1) {
                $maiorRentPorRisco[] = ['rentab' => round($rentabAlocacao, 2), 'risco' => round($riscoAlocacao, 2)];
                //echo('Risco:' . $riscoAlocacao . ', Incluído novo:' . $rentabAlocacao . "<br/>");
            } else if (round($rentabAlocacao, 2) > $maiorRentPorRisco[$idxMaiorRent]['rentab']) {
                //echo('Risco:' . $riscoAlocacao . ', era:' . $maiorRentPorRisco[$idxMaiorRent]['rentab'] . " -> ");
                $maiorRentPorRisco[$idxMaiorRent]['rentab'] = round($rentabAlocacao, 2);
                //echo($maiorRentPorRisco[$idxMaiorRent]['rentab'] . '<br/>');
            }

            $alocacoes['alocacoes'][] = ['id' => $iteracoes, 'pesos' => $pesos, 'rentabilidade' => $rentabAlocacao, 'risco' => $riscoAlocacao, 'numFundos' => $countRec, 'inFronteira' => 0];
            //
            // calcula próximo arranjo
            $iteracoes++;
            $pos = $countRec - 2;
            $done = false;
            while (!$done && $pos >= 0) {
                $sum = 0;
                for ($i = 0; $i < $pos; $i++) {
                    $sum += $pesos[$i];
                }
                $esteMax = $max - $sum - ($min * ($countRec - $pos - 2));
                //echo("pos:".$pos.", sum:".$sum.", pes[]:".$pesos[$pos].", comp:".$esteMax."<br/>");
                if ($pesos[$pos] >= $esteMax - $this->tolerancia) {
                    //if ($this->equals($pesos[$pos], $esteMax)) {
                    $pesos[$pos] = $min;
                    $pos--;
                } else {
                    $pesos[$pos] += $p;
                    $done = true;
                }
            }
            $sum = 0;
            for ($i = 0; $i <= $countRec - 2; $i++) {
                $sum += $pesos[$i];
            }
            $pesos[$countRec - 1] = 1 - $sum;
            //echo("peso total:".array_sum($pesos)." último peso:".$pesos[$coufalsentRec-1].", p's:".($pesos[$countRec-1]/$p)."<br/>");
        } while ($pos >= 0 && $iteracoes < 1e6);

        //echo('maiorRentPorRisco');
        //var_dump($maiorRentPorRisco);
        //echo('menorRiscoPorRent');
        //var_dump($menorRiscoPorRent);
        //exit();

        for ($i = 0; $i < count($alocacoes['alocacoes']); $i++) {
            $idxMaiorRent = $this->meu_array_search(round($alocacoes['alocacoes'][$i]['risco'], 2), $maiorRentPorRisco, 'risco');
            $maiorRent = $maiorRentPorRisco[$idxMaiorRent]['rentab'];
            $esteRent = round($alocacoes['alocacoes'][$i]['rentabilidade'], 2);
            //if (abs($maiorRent - $esteRent) < $this->tolerancia) {
            //	$alocacoes['alocacoes'][$i]['inFronteira'] ++;
            //}
            $idxMenorRisco = $this->meu_array_search(round($alocacoes['alocacoes'][$i]['rentabilidade'], 2), $menorRiscoPorRent, 'rentab');
            $menorRisco = $menorRiscoPorRent[$idxMenorRisco]['risco'];
            $esteRisco = round($alocacoes['alocacoes'][$i]['risco'], 2);
            if ((abs($menorRisco - $esteRisco) < $this->tolerancia) && (abs($maiorRent - $esteRent) < $this->tolerancia)) { // && (abs($maiorRent - $esteRent) < $this->tolerancia)) {
                $alocacoes['alocacoes'][$i]['inFronteira']++;
            }
        }

        $this->set(compact('alocacoes'));
    }
}
