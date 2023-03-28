<?php

namespace Gsferro\ResourceCrudEasy\Controllers;

use Freshbitsweb\Laratables\Laratables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/*
|---------------------------------------------------
| Reuso para a rota grid
|---------------------------------------------------
| TODO ira para o pacote FilterEasy
*/

class DatatablesController extends Controller
{
    public function __invoke(Request $request)
    {
        // encapsulamento
        $dados = $request->all();

        # Obrigação de enviar a model
        $hash = $dados[ 'hash' ] ?? null;
        // encrypt
        if (filled($hash)) {
            // pegando a model enviada e decodificando
            $model = "App\\Model\\". Crypt::decryptString($hash);
        }

        // TODO tratar caso model não exista ou tente acessar onde não deveria
        if (!class_exists($model)) {
            return "Hash inválido!";
        }
        
        // caso tenha sido informado o form dentro da chamada do DataTable
        return Laratables::recordsOf($model, function ($q) use ($dados, $hash) {
            $form               = $dados[ 'form' ] ?? null;
            $withoutGlobalScope = $dados[ 'withoutGlobalScope' ] ?? null;
            $sessionName        = "{$hash}.filter";

            /*
            |---------------------------------------------------
            | Verifica se tem algum scopeGlobal para retirar
            |---------------------------------------------------
            */
            if (!is_null($withoutGlobalScope)) {
                $q = $q->withoutGlobalScope($withoutGlobalScope);
            }

            /*
            |---------------------------------------------------
            | para não recarregar
            |---------------------------------------------------
            */
            // todo melhorar
            if (!is_null($form)) {
                $dados = [];
                // prepara os dados
                foreach ($form as $index => $dado) {
                    if (!is_null($dado[ "value" ])) {
                        // verifica se eh um array
                        if (strpos($dado[ "name" ], "[]") > 0) {
                            $name             = str_replace("[]", "", $dado[ "name" ]);
                            $dados[ $name ][] = $dado[ "value" ];
                        } else {
                            $dados[ $dado[ "name" ] ] = $dado[ "value" ];
                        }
                    }
                }

                // processo de datatable
                session()->put($sessionName, $dados);

                return $q->filterEasy($dados);
            }

            // ao recarregar a tela apos já ter ocorrido um filtro
            $session = session()->get($sessionName);
            if (!empty($session)) {
                return $q->filterEasy($session);
            }

            // 1º vez
            session()->forget($sessionName);
            return $q;
        });
    }
}
