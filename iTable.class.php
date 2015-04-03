<?php

/*
 * Copyright (c) 2015, Alexis
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Description of iTable
 * $javascript: Codigo JavaScript que sera creado globalmente en la clase.
 * $html: Codigo HTM que sera creado globalmente en la clase.
 * $cm:Column model 
 * @author Alexis
 */
class iTable {

  var $id;
  var $html;
  var $javascript;
  var $columnas;
  var $botones;
  var $url;
  var $perPageOptions = array(25, 50, 100, 200, 400, 800, 1600, 3200);
  var $perPage;
  var $page;
  var $pagination;
  var $serverSort;
  var $showHeader;
  var $alternaterows;
  var $sortHeader;
  var $resizeColumns;
  var $multipleSelection;
  var $width;
  var $height;

  function iTable($datos) {
    $this->id = "tabla" . $datos['id'];
    $this->columnas = array();
    $this->botones = array();
    $this->javascript = array();
    $this->html = array();
    $this->url = $datos['url'];
    $this->perPageOptions = (isset($datos['perPageOptions'])) ? $datos['perPageOptions'] : $this->perPageOptions;
    $this->page = (isset($datos['page'])) ? $datos['page'] : "1";
    $this->pagination = (isset($datos['pagination'])) ? $datos['pagination'] : "true";
    $this->serverSort = (isset($datos['serverSort'])) ? $datos['serverSort'] : "false";
    $this->showHeader = (isset($datos['sortHeader'])) ? $datos['sortHeader'] : "true";
    $this->alternaterows = (isset($datos['sortHeader'])) ? $datos['sortHeader'] : "true";
    $this->sortHeader = (isset($datos['sortHeader'])) ? $datos['sortHeader'] : "false";
    $this->resizeColumns = (isset($datos['resizeColumns'])) ? $datos['resizeColumns'] : "false";
    $this->multipleSelection = (isset($datos['multipleSelection'])) ? $datos['multipleSelection'] : "true";
    $this->width = (isset($datos['width'])) ? $datos['width'] : "\$('central').getSize().x";
    $this->height = (isset($datos['height'])) ? $datos['height'] : "\$('central').getSize().y";
  }

  /**
   * Este metodo permite crear la estructura en JavaScript correspondiente a una columna.
   * @param type $id: Identidad del elemento.
   * @param type $header: Texto que se visualizara en el encabezado de la columna.
   * @param type $dataIndex: Indice del valor que se leera para los datos de la columna.
   * @param type $dataType: Tipo de dato que se visualizara (number,string,date,currency),
   * @param type $width: Ancho en pixels de la columna como elemento grafico.
   * @param type $align: Alineación de los datos contenidos en la columna (left, center, right).
   * @param type $hidden: Valor boleano que indica si la columna esta oculta o no (true/false).
   * @return type String en formato JavaScript.
   */
  function columna($id, $header, $dataIndex, $dataType, $width, $align, $hidden) {
    $columna = array(
        "id" => $id,
        "header" => $header,
        "dataIndex" => $dataIndex,
        "dataType" => $dataType,
        "width" => $width,
        "align" => $align,
        "hidden" => $hidden
    );
    return($this->columnas($columna));
  }

  /**
   * permite agregar las columnas al modelo general $columnas
   * @param type $columna
   * @return type
   */
  function columnas($columna) {
    return(array_push($this->columnas, $columna));
  }

  /**
   * Permite crear un boton completo incluido su comportamiento JavaScript
   * @param type $id
   * @param type $name
   * @param type $bclass
   * @param type $onclick
   * @return type
   */
  function boton($id, $texto, $indice, $funcion, $bclass) {
    $ejecutar = $this->set_onClick($id, $indice, $funcion);
    $boton = array('id' => $id, 'name' => $texto, "bclass" => $bclass, "onclick" => $ejecutar);

    $this->botones($boton);
  }

  /**
   * Permite adicionar botones a la estructura.
   * @param type $boton
   * @return type
   */
  function botones($boton) {
    return(array_push($this->botones, $boton));
  }

  function get_Columnas() {
    $columnas = $this->columnas;
    $js = array();
    foreach ($columnas as $valor) {
      array_push($js, "\n\t{"
              . "'header':\"" . $valor['header'] . "\", "
              . "'dataIndex':'" . $valor['dataIndex'] . "',"
              . "'dataType':'" . $valor['dataType'] . "',"
              . "'width':'" . $valor['width'] . "',"
              . "'align':'" . $valor['align'] . "',"
              . "'hidden':" . $valor['hidden'] . ""
              . "}");
    }
    return("[" . implode(",", $js) . "\n]");
  }

  /**
   * Retorna la estructura correspondiente a los botones mediante una estructura tipo Hash
   * @return type Hash.
   */
  function get_Botones() {
    $botones = $this->botones;
    $js = array();
    $conteo = 0;
    foreach ($botones as $valor) {
      $js[$conteo] = ("\n\t{'name':'" . $valor['name'] . "','bclass':'" . $valor['bclass'] . "','onclick':" . $valor['onclick'] . "}");
      $conteo++;
    }
    return("[" . implode(",", $js) . "\n]");
  }

  function get_perPageOptions() {
    return("[" . implode(",", $this->perPageOptions) . "]");
  }

  function set_onClick($id, $indice, $funcion) {
    $nombre = $this->id . "_" . $id . "_onClick";
    if (!empty($indice)) {
      $js = "\n\tfunction " . $nombre . "(button, grid) {"
              . "\n\t\tvar indices =i" . $this->id . ".getSelectedIndices();"
              . "\n\t\tif (indices.length==0){"
              . "\n\t\tMUI.Aplicacion_Advertencia_Seleccion();"
              . "\n\t\treturn;"
              . "\n\t\t}"
              . "\n\t\tvar indice= '';"
              . "\n\t\tfor (var i = 0; i < indices.length; i++) {"
              . "\n\t\tindice= i" . $this->id . ".getDataByRow(indices[i])." . $indice . ";"
              . "\n\t\t" . $funcion . "(indice,'i".$this->id."');"
              . "\n\t\t}"
              . "\n\t}";
    } else {
      $js = "\n\tfunction " . $nombre . "(button, grid) {"
              . "\n\t\t" . $funcion . "('i".$this->id."');"
              . "\n\t}";
    }
    array_push($this->javascript, $js);
    return($nombre);
  }

  function set_Estructura() {
    $js="window.addEvent('domready', function() {";
    $js.= "\ni".$this->id."= new iTable('" . $this->id . "',\n{"
            . "\n'columnModel':" . $this->get_Columnas() . ","
            . "\n'buttons':" . $this->get_Botones() . ","
            . "\n'url':'" . $this->url . "',"
            . "\n'perPageOptions':" . $this->get_perPageOptions() . ","
            . "\n'perPage':" . $this->perPageOptions[0] . ","
            . "\n'page': " . $this->page . ","
            . "\n'pagination':" . $this->pagination . ","
            . "\n'serverSort': " . $this->serverSort . ","
            . "\n'showHeader': " . $this->showHeader . ","
            . "\n'alternaterows': " . $this->alternaterows . ","
            . "\n'sortHeader': " . $this->sortHeader . ","
            . "\n'resizeColumns': " . $this->resizeColumns . ","
            . "\n'multipleSelection': " . $this->multipleSelection . ","
            . "\n'width': " . $this->width . ","
            . "\n'height': " . $this->height . ""
            . "\n});"
            . "\ni".$this->id.".addEvent('click', onGridSelect);";
    $js.= "\n});";
    return(array_push($this->javascript, $js));
  }

  function get_Javascript() {
    $js = "\n<script type=\"text/javascript\">";
    $js.="\n\tfunction onGridSelect(evt){\n\t\tvar str = 'row: '+evt.row+' indices: '+evt.indices; \n\t\tstr += ' id: '+evt.target.getDataByRow(evt.row).id;\n\t}\n";
    $js.=implode("\n", $this->javascript);
    $js.="\n</script>";
    return($js);
  }

  function get_Html() {
    return("\n<div id=\"" . $this->id . "\" style=\"width:100%\" ></div>");
  }

  function generar() {
    $this->set_Estructura();
    echo($this->get_Javascript());
    echo($this->get_Html());
  }

}

