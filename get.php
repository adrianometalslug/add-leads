<?php

$filtro = array('nome', 'email', 'telefone', 'time', 'profissionalizantes');

if (@$_POST) {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
}

/**
 * Faz a conex�o com a Server e retorna um XML de campos
 * @return type
 */
class Form
{

    private $br;
    private $xml;

    /**
     * Abre o form setando os par�metros necess�rios
     * @param type $name
     * @param type $id
     * @param type $method
     * @param type $action
     */
    public function formOpen($name = null, $id = null, $method = null, $action = null)
    {
        echo "<form name=\"{$name}\" id=\"{$id}\" method=\"{$method}\" action=\"{$action}\">";
    }

    /**
     * Fecha o form
     */
    public function formClose()
    {
        echo "</form>";
    }

    /**
     * Imprime o campo HTML
     * @param type $formato
     * @param type $tipo
     * @param type $name
     * @param type $indice
     * @param type $label
     * @param type $obrigatorio
     * @param type $opcoes
     * @return type
     */
    public function campo($formato, $tipo, $name, $indice, $label, $obrigatorio, $opcoes)
    {
        $require = $obrigatorio > 0 ? "required=\"true\"" : NULL;
        if ($formato == "input") {
            return "<label for=\"$indice\">{$label}:</label>{$this->br}<input type=\"$tipo\" name=\"$name\" id=\"$indice\" $require>";
        } else {
            $string = "<label for=\"$indice\">{$label}:</label>{$this->br}<select name=\"$name\" id=\"$indice\" $require>";
            foreach ($opcoes as $key => $value):
                $string .= "<option vlue=\"{$key}\">{$value}</option>";
            endforeach;
            return $string .= "</select>";
        }
    }

    /**
     * Insere uma quebra de linha no label
     * @param type $value
     */
    public function setBr($value)
    {
        $this->br = $value > 0 ? '<br/>' : NULL;
    }

    /**
     * Retorna o corpo do xml
     * @return type
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * conecta no webserver e alimenta o artributo xml
     */
    public function connect()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://leads.evolutime.net.br/api/campo/campos?format=xml");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $this->xml = simplexml_load_string($result);
    }

}

$form = new Form;
$form->connect();
$form->setBr(true);


###############
# FORMUL�RIO
###############
# inicia o form
$form->formOpen(NULL, "form_leads", "POST", NULL);

# formato {input/select}
# tipo {email, number, password...}
foreach ($form->getXml() as $value):
    # verifica se pode ser impresso
    if (in_array($value->item->name, $filtro)) {
        # gera as op��es
        $opcoes = array();
        foreach ($value->item[1]->opcoes->opco as $item):
            $opcoes[(int) $item->id] = $item->value;
        endforeach;
        echo $form->campo($value->item->formato, $value->item->tipo, $value->item->name, $value->item->indice, $value->item->label, $value->item->obrigatorio, $opcoes);
        echo "<br>";
    }
endforeach;
echo "<br>";
echo "<input type=\"submit\" value=\"Cadastrar\" />";

# fecha o form
$form->formClose();
