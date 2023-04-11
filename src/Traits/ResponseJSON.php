<?php

namespace Gsferro\ResourceCrudEasy\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Trait ResponseJSON
 *
 * Reuso e padronização de resposta json
 *
 * @package Gsferro\ResourceCrudEasy
 */
trait ResponseJSON
{
    protected int    $code     = 404;
    protected string $msgTrue  = "Realizado com sucesso";
    protected string $msgFalse = "Falhou ao realizar";
    protected array  $result   = [];

    protected function codeError(int $code)
    {
        $this->code = $code;
    }

    protected function addResult(array $result)
    {
        $this->result = $result;
    }

    /**
     * Retorna um array em formato json com status code
     *
     * @param array $error
     * @param array $data
     * @param int|null $code
     * @return JsonResponse
     */
    protected function error(array $error = [], array $data = [], int $code = null): JsonResponse
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

    protected function validateFails($error = null, $data = []): JsonResponse
    {
        return $this->error($error, $data, 422);
    }

    /**
     * Retorna um array com os dados que é convertido a json como sucesso
     *
     * @param $result
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    protected function success($result, string $message = null, int $code = 200): JsonResponse
    {
        $res = [
            'success' => true,
            'data'    => $result,
            'message' => $message ?? $this->msgTrue,
            'code'    => $code
        ];

        // TODO analise de como enviar o feedback
        session()->flash('msgSucesso', $res["message"]);

        return response()->json($res, $code);
    }

    /**
     * verifica a operação e devolve a resposta
     *
     * @param $operation
     * @param null $msgTrue
     * @param null $msgFalse
     * @return array|JsonResponse
     */
    protected function verify($operation, $msgTrue = null, $msgFalse = null): JsonResponse|array
    {
        return $operation
            ? $this->success($this->result, $msgTrue)
            : $this->error($msgFalse, $this->result, $this->code);
    }
}
