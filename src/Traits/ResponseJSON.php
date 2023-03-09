<?php

namespace Gsferro\ResourceCrudEasy\Traits;

/**
 * Trait ResponseJSON
 *
 * Reuso e padronização de resposta json
 *
 * @package Gsferro\ResourceCrudEasy
 */
trait ResponseJSON
{
    protected $code    = 404;
    protected $msgTrue = "Realizado com sucesso";
    protected $msgFalse  = "Falhou ao realizar";
    protected $result    = [];

    // herdando de ResponseJSON caso queira mudar o code response
    protected function codeError($code)
    {
        $this->code = $code;
    }
    // herdando de ResponseJSON caso queira mudar o code response
    protected function addResult($result)
    {
        $this->result = $result;
    }

    /**
     * Retorna um array em formato json com status code
     *
     * @param $error
     * @param array $data
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($error = [], array $data = [], $code = null)
    {
        // caso queira passar direto ou pegar default
        $code = $code ?? $this->code;

        $res = $error + [
            'success' => false,
            'message' => $this->msgFalse,
            'code'    => $code
        ];

        if (filled($data) || filled($this->result)) {
            $res[ 'data' ] = $data ?? $this->result;
        }

        // TODO analise de como enviar o feedback
        session()->flash('msgErro', $res["message"]);

        return response()->json($res, $code);
    }

    protected function validateFails($error = null, $data = [])
    {
        return $this->error($error, $data, 422);
    }

    /**
     * Retorna um array com os dados que é convertido a json como sucesso
     *
     * @param $result
     * @param string $message
     * @return array json
     */
    protected function success($result, $message = null)
    {
        $res = [
            'success' => true,
            'data'    => $result,
            'message' => $message ?? $this->msgTrue,
        ];

        // TODO analise de como enviar o feedback
        session()->flash('msgSucesso', $res["message"]);

        return $res;
    }
    ///////////////////////////////////////////////// resposta
    /**
     * verifica a operação e devolve a resposta
     *
     * @param $operation
     * @param null $msgTrue
     * @param null $msgFalse
     * @return array|\Illuminate\Http\JsonResponse
     */
    protected function verify($operation, $msgTrue = null, $msgFalse = null)
    {
        return $operation 
            ? $this->success($this->result, $msgTrue) 
            : $this->error($msgFalse, $this->result, $this->code);
    }
}
