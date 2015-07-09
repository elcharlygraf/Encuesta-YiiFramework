<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
  * 	Files Purpose: lots of common functions
 */
class SurveyDynamic extends LSActiveRecord
{
    protected static $sid = 0;

    /**
     * Returns the static model of Settings table
     *
     * @static
     * @access public
     * @param int $surveyid
     * @return SurveyDynamic
     */
    public static function model($sid = NULL)
    {         
        $refresh = false;
        if (!is_null($sid)) {
            self::sid($sid);
            $refresh = true;
        }
        
        $model = parent::model(__CLASS__);
        
        //We need to refresh if we changed sid
        if ($refresh === true) $model->refreshMetaData();
        return $model;
    }

    /**
     * Sets the survey ID for the next model
     *
     * @static
     * @access public
     * @param int $sid
     * @return void
     */
    public static function sid($sid)
    {
        self::$sid = (int) $sid;
    }

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{survey_' . self::$sid . '}}';
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * Insert records from $data array
     *
     * @access public
     * @param array $data
     * @return boolean
     */
    public function insertRecords($data)
    {
        $record = new self;
        foreach ($data as $k => $v)
        {
            $search = array('`', "'");
            $k = str_replace($search, '', $k);
            $v = str_replace($search, '', $v);
            $record->$k = $v;
        }

        try
        {
            $record->save();
            return $record->id;
        }
        catch(Exception $e)
        {
            return false;
        }
        
    }

    /**
     * Deletes some records from survey's table
     * according to specific condition
     *
     * @static
     * @access public
     * @param array $condition
     * @return int
     */
    public static function deleteSomeRecords($condition = FALSE)
    {
        $survey = new SurveyDynamic;
        $criteria = new CDbCriteria;

        if ($condition != FALSE)
        {
            foreach ($condition as $column => $value)
            {
                return $criteria->addCondition($column . "=`" . $value . "`");
            }
        }

        return $survey->deleteAll($criteria);
    }
    
    /**
     * Return criteria updated with the ones needed for including results from the timings table
     *
     * @param CDbCriteria|string $criteria
     *
     * @return CDbCriteria
     */
    public function addTimingCriteria($condition)
    {
        $newCriteria = new CDbCriteria();
        $criteria = $this->getCommandBuilder()->createCriteria($condition);

        if ($criteria->select == '*')
        {
            $criteria->select = 't.*';
        }
		$alias = $this->getTableAlias();

        $newCriteria->join = "LEFT JOIN {{survey_" . self::$sid . "_timings}} survey_timings ON $alias.id = survey_timings.id";
        $newCriteria->select = 'survey_timings.*';  // Otherwise we don't get records from the token table
        $newCriteria->mergeWith($criteria);

        return $newCriteria;
    }

    /**
     * Return criteria updated with the ones needed for including results from the token table
     *
     * @param CDbCriteria|string $criteria
     *
     * @return CDbCriteria
     */
    public function addTokenCriteria($condition)
    {
        $newCriteria = new CDbCriteria();
        $criteria = $this->getCommandBuilder()->createCriteria($condition);
        $aSelectFields=Yii::app()->db->schema->getTable('{{survey_' . self::$sid  . '}}')->getColumnNames();
        $aSelectFields=array_diff($aSelectFields, array('token'));
        $aSelect=array();
		    $alias = $this->getTableAlias();
        foreach($aSelectFields as $sField)
            $aSelect[]="$alias.".Yii::app()->db->schema->quoteColumnName($sField);
        $aSelectFields=$aSelect;   
		    $aSelectFields[]="$alias.token";

        if ($criteria->select == '*')
        {
            $criteria->select = $aSelectFields;
        }

        $newCriteria->join = "LEFT JOIN {{tokens_" . self::$sid . "}} tokens ON $alias.token = tokens.token";

        $aTokenFields=Yii::app()->db->schema->getTable('{{tokens_' . self::$sid . '}}')->getColumnNames();
        $aTokenFields=array_diff($aTokenFields, array('token'));
        
        $newCriteria->select = $aTokenFields;  // Otherwise we don't get records from the token table
        $newCriteria->mergeWith($criteria);

        return $newCriteria;
    }
    
    public static function countAllAndPartial($sid)
    {
        $select = array(
            'count(*) AS cntall',
            'sum(CASE 
                 WHEN '. Yii::app()->db->quoteColumnName('submitdate') . ' IS NULL THEN 1
                          ELSE 0
                 END) AS cntpartial',
            );
        $result = Yii::app()->db->createCommand()->select($select)->from('{{survey_' . $sid . '}}')->queryRow();
        return $result;
    }
    
    public function isCompleted($srid)
    {
        static $resultCache = array();
        
        $sid = self::$sid;
        if (array_key_exists($sid, $resultCache) && array_key_exists($srid, $resultCache[$sid])) {
            return $resultCache[$sid][$srid];
        }
        $completed=false;

        if(Yii::app()->db->schema->getTable($this->tableName())){
            $data=Yii::app()->db->createCommand()
                ->select("submitdate,
                635296X1X1,
                635296X1X3,
                635296X1X6,
                635296X2X12,
                635296X1X9,
                635296X1X10,
                635296X1X8,
                635296X2X20,
                635296X1X2,
                635296X2X97SQ001,
                635296X2X97SQ002,
                635296X2X97SQ003,
                635296X3X28SQ001,
                635296X3X28SQ002,
                635296X3X28SQ003,
                635296X3X28SQ004,
                635296X3X41SQ005,
                635296X3X41SQ006,
                635296X3X41SQ007,
                635296X3X41SQ008,
                635296X3X48SQ009,
                635296X3X48SQ010,
                635296X3X48SQ011,
                635296X3X48SQ012,
                635296X5X65SQ001,
                635296X5X65SQ002,
                635296X5X65SQ003,
                635296X5X65SQ004,
                635296X5X65SQ005,
                635296X5X65SQ006,
                635296X5X65SQ007,
                635296X5X65SQ008,
                635296X5X65SQ009,
                635296X5X65SQ010,
                635296X6X84SQ001,
                635296X6X84SQ002,
                635296X6X84SQ003,
                635296X6X84SQ004,
                635296X6X84SQ006,
                635296X6X82SQ001,
                635296X4X2361_11,
                635296X2X21SQ001,
                635296X2X21SQ002,
                635296X2X21SQ003,
                635296X2X21SQ004,
                635296X2X21SQ005,
                635296X2X21SQ006,
                635296X4X2362_11,
                635296X4X2361_12,
                635296X4X2362_12,
                635296X4X2363_12,
                635296X4X2364_12,
                635296X4X2365_12,
                635296X4X2366_12,
                635296X4X2367_12,
                635296X4X2368_12,
                635296X4X2361_10,
                635296X4X2361_10,
                635296X4X2363_10,
                635296X4X2363_11,
                635296X4X2364_10,
                635296X4X2364_11,
                635296X4X2365_10,
                635296X4X2365_11,
                635296X4X2366_10,
                635296X4X2366_11,
                635296X4X2367_10,
                635296X4X2367_11,
                635296X4X2368_10,
                635296X4X2368_11
                635296X4X2362_10")


                ->from($this->tableName())
                ->where('id=:id', array(':id'=>$srid))
                ->queryRow();

            if($data && $data['submitdate'])
            {
                $completed=true;
                //FUNCION DE SUMATORIA
                      function contador($var, $vueltas) 
                      {
                              $Totalsuma = 0;
                              for($i=0; $i<$vueltas; $i++){

                                  switch ($var[$i]) 
                                  {
                                      case 'A1':
                                          $Totalsuma += $Suma[$i]=1;
                                          break;

                                      case 'A2':
                                          $Totalsuma += $Suma[$i]=2;
                                          break;
                                      
                                      case 'A3':
                                          $Totalsuma += $Suma[$i]=3;
                                          break;
                                      
                                      case 'A4':
                                          $Totalsuma += $Suma[$i]=4;
                                          break;
                                      
                                      case 'A5':
                                          $Totalsuma += $Suma[$i]=5;
                                          break;
                                      
                                      case 'A6':
                                          $Totalsuma += $Suma[$i]=6;
                                          break;
                                      
                                      case 'A7':
                                          $Totalsuma += $Suma[$i]=7;
                                          break;

                                      default:
                                          $Totalsuma += $Suma[$i]=0;
                                          break;
                                  }

                              }
                              return $Totalsuma;
                      }
                //FIN 

                //FUNCION DE PORCENTAJE
                   function Porcentaje($varx, $varTotal)
                      {
                      return round( ($varx/$varTotal)*100,0 );    
                      }
                //FIN 
                
                //SACA LA EDAD
                        $cumple  = $data['635296X1X2'];
                        $cumpleExtract = explode("-", $cumple);
                        $edad = date('Y')-$cumpleExtract[0]; 
                //FIN 

                //FECHA EN ESPAÑOL
                    $dia=date("l");
                    if ($dia=="Monday") $dia="Lunes";
                    if ($dia=="Tuesday") $dia="Martes";
                    if ($dia=="Wednesday") $dia="Miércoles";
                    if ($dia=="Thursday") $dia="Jueves";
                    if ($dia=="Friday") $dia="Viernes";
                    if ($dia=="Saturday") $dia="Sabado";
                    if ($dia=="Sunday") $dia="Domingo";

                    $mes=date("F");
                    if ($mes=="January") $mes="Enero";
                    if ($mes=="February") $mes="Febrero";
                    if ($mes=="March") $mes="Marzo";
                    if ($mes=="April") $mes="Abril";
                    if ($mes=="May") $mes="Mayo";
                    if ($mes=="June") $mes="Junio";
                    if ($mes=="July") $mes="Julio";
                    if ($mes=="August") $mes="Agosto";
                    if ($mes=="September") $mes="Setiembre";
                    if ($mes=="October") $mes="Octubre";
                    if ($mes=="November") $mes="Noviembre";
                    if ($mes=="December") $mes="Diciembre";
                //FIN

                //SACA SEXO
                  /*
                  `635296X1X3` = "genero"
                  */      
                        if($data['635296X1X3']=='M')
                        { 
                          $sexo="Estimado"; } 
                        else
                        { 
                          $sexo="Estimada"; }
                //FIN

                //EXPERIENCIA
                  /*
                  `635296X1X8`  = "¿Tienes experiencia de trabajo en el área de tu emprendimiento?"
                  `635296X1X9`  = "el tiempo"
                  `635296X1X10` = "¿Tienes experiencia en iniciar otros negocios o emprendimientos?"
                  */    
                          if($data['635296X1X8']=='Y' and $data['635296X1X10']=='N') 
                              { 
                                if($data['635296X1X9']>=10)
                                {
                                  $experiencia = "SI"; 
                                }
                                else{
                                  $experiencia = "NO";
                                }
                              } 
                          elseif($data['635296X1X8']=='Y' and $data['635296X1X10']=='Y') 
                              { 
                                if($data['635296X1X9']>=10)
                                {
                                  $experiencia = "SI"; 
                                }
                                else{
                                  $experiencia = "NO";
                                }
                              }     
                          elseif($data['635296X1X8']=='N' and $data['635296X1X10']=='Y')
                              {  
                                $experiencia = "SI"; 
                              } 
                          elseif($data['635296X1X8']=='N' and $data['635296X1X10']=='N')
                              {  
                                $experiencia = "NO"; 
                              }    
                //FIN

                //SACA ETAPA DE TU PROYECTO
                  /*
                  `635296X2X20` = "Seleccione una de las siguientes opciones"
                    A1 = Pensando
                    A2 = Creando
                    A3 = Equilibrando
                    A4 = Consolidando
                    A5 = Transformando
                  */   
                      //PENSANDO           
                      if($data['635296X2X20']=='A1')
                      {
                        
                        $etapa= "
                            <tr>
                              <td width='130' align='left' valign='top'>Pensando</td>
                              <td width='360' align='left' valign='top'>
                              <p align='justify'>Todavía estas dando vueltas en tu cabeza  a una idea de negocio que crees que es muy buena. Será importante que le des  forma a esta idea de negocio mediante la utilización de un Plan de negocios. <br>
                                Ser emprendedor es un tema de acción, así  que ponte en acción. Te sugiero además buscar información sobre &ldquo;hipótesis de  mercado para emprendedores&rdquo;, esta información te ayudará a definir mejor tu  idea de negocio con una orientación práctica, es decir, para la acción.</p>
                                Recuerda,  esta encuesta ha sido diseñada para emprendedores que ya han comenzado a poner  en marcha su negocio. De todo modos, muchas gracias por tu interés. Ánimo y  pasa a la acción.
                              </td>
                            </tr>";
                        $nameEtapa = "PENSANDO";    
                      }
                      //CREANDO
                      elseif($data['635296X2X20']=='A2')
                      {
                        $etapa= "
                            <tr>
                              <td align='left' valign='top'>Creando</td>
                              <td align='left' valign='top'><p align='justify'>Recién has comenzado a poner en marcha tu  negocio y estás sintiendo mucho entusiasmo. Tu cabeza no para de pensar en todo  lo que tienes que hacer. Pero ahora empiezas a ver las diferencias entre  planear y llevar a la práctica esos planes. Muchos problemas nuevos comienzan a  aparecer. </p>
                              Además  del entusiasmo también experimentas inseguridades. Seguramente estas a punto de  dar el salto dejando tu trabajo seguro o ya diste ese salto y estás 
                              <p>dedicado al 100% en tu negocio. </p>
                              En esta etapa necesitarás capacitarte y asesorarte para aprovechar al máximo tu  tiempo y que este sea eficiente. Es muy importante que vayas dándole forma a tu  modelo de negocio y apliques y ajustes tu plan de negocios.</td>
                            </tr>";
                        $nameEtapa = "CREANDO";        
                      }
                      //EQUILIBRANDO
                      elseif($data['635296X2X20']=='A3')
                      {
                        $etapa= "
                            <tr>
                              <td align='left' valign='top'>Equilibrando</td>
                              <td align='left' valign='top'><p align='justify'>Esta etapa está caracterizada por la  búsqueda de la rentabilidad. Ya tu negocio o emprendimiento social está  marchando pero debes hacerlo sostenible. Y esa sostenibilidad económica  incluye, por supuesto, lograr los ingresos que necesitas para satisfacer tus  propias necesidades.</p>
                              En esta etapa vas a concentrarte en las ventas, la distribución y la generación de  ingresos que necesitas. Así como también en cómo agilizar el crecimiento de tu  negocio.  Vas a necesitar centrarte en  los cuellos de botella que demoran el crecimiento de tu emprendimiento. Y vas a  tener que evitar dispersarte. En esta etapa vas a poner a prueba tu modelo de  negocio ante el crecimiento. </td>
                            </tr>";
                        $nameEtapa = "EQUILIBRANDO";        
                      }
                      //CONSOLIDANDO
                      elseif($data['635296X2X20']=='A4')
                      {
                        $etapa= "
                            <tr>
                              <td align='left' valign='top'>Consolidando</td>
                              <td align='left' valign='top'><p align='justify'>Haber llegado a esta etapa significa que  cuentas con una actividad que ha experimentado un crecimiento importante y te  estas destacando en el mercado. Esto requiere de una mayor consolidación y  posicionamiento. Para lograr esto vas a necesitar gestionar estratégicamente tu  empresa en base al capital humano y financiero. </p>
                              El tamaño que tu organización está logrando implica retos que ya no pueden ser  cubiertos con un manejo intuitivo. Requieres de soluciones eficientes y de  experiencia profesional. Tu aparato administrativo tiende a crecer obligando a  replantear varios aspectos de tu modelo de negocio. </td>
                            </tr>";
                        $nameEtapa = "CONSOLIDANDO";        
                      }
                      //TRANSFORMANDO
                      elseif($data['635296X2X20']=='A5')
                      {
                        $etapa= "
                            <tr>
                              <td align='left' valign='top'>Transformando</td>
                              <td align='left' valign='top'align='justify'>Son  muy pocas organizaciones las que han llegado a consolidarse y superar los retos  del crecimiento para plantearse metas más retadoras. Metas como querer innovar,  explorar nuevos mercados, desarrollar nuevos productos o generar más empleo.  Lograr que estas innovaciones sean sostenibles en el tiempo es lo más difícil.  Normalmente las organizaciones que han crecido con éxito tienden a replicar sus  antiguas fórmulas de éxito. Hay que luchar contra el confort alcanzado que  desanima a asumir nuevos riesgos que se miran como innecesarios. Sin las  capacidades adecuadas estas ambiciones consumen con rapidez el capital  acumulado. Con el equipo y talento correcto estas organizaciones pueden dar un  salto. </td>
                            </tr>";
                        $nameEtapa = "TRANSFORMANDO";        
                      }
                //FIN  

                //FORMALIZACION DE LA EMPRESA
                  /*
                    ¿Has logrado formalizar tu negocio?
                    `635296X2X97SQ001` = "Mi negocio cuenta con los requisitos legales."
                    `635296X2X97SQ002` = "Estoy en proceso de completar varios requisitos legales."
                    `635296X2X97SQ003` = "No, más adelante."
                    ----------------------------------------------------------------------------------
                    `635296X2X20` = "Seleccione una de las siguientes opciones"  
                  */            
                      if($data['635296X2X97SQ001']=='Y' or $data['635296X2X97SQ002']=='Y')
                      {
                        $formalEmpresa = "
                        <tr>
                          <td align='justify'>Felicitaciones,  haber formalizado tu empresa es un paso importante y te permite acceder a  diversos beneficios y programas de apoyo además de que es la mejor manera de  contribuir económica con el desarrollo de tu país y sociedad.</td>
                        </tr>";
                      }
                      elseif($data['635296X2X97SQ003']=='N')
                      {
                        if($data['635296X2X20']=='A1' or $data['635296X2X20']=='A2')
                        {
                          $formalEmpresa = 
                            "<p>Si bien todavía no has formalizado tu empresa estas en un bueno momento para iniciarlo. Recuerda que hay diferentes organizaciones que ayudan a hacer ágil este proceso y te brindan asesoría como parte de su servicio.</p>
                             <p>Si estas en Lima te sugerimos visitar el Centro de Negocios de COFIDE, ubicado en el distrito de San Isidro. Por un precio muy cómodo te asesorarán para poder formalizar tu negocio. También puedes acudir a las distintas oficinas del Servicio de Constitución de Empresas del Ministerio de la Producción, que además tiene un servicio en línea (pero te aconsejamos hacerlo presencialmente ya que hemos detectado problemas de desconexión en sus servicios en línea).</p>";
                        }
                      }
                      elseif($data['635296X2X20']=='A3' or $data['635296X2X20']=='A4' or $data['635296X2X20']=='A5')
                      {
                          $formalEmpresa = 
                            "<p>Estas en una etapa en la que es muy importante que te formalices. El no hacerlo puede ocasionarte problemas y multas. Inicia cuanto antes este proceso.</p>
                             <p>Si estas en Lima te sugerimos visitar el Centro de Negocios de COFIDE, ubicado en el distrito de San Isidro. Por un precio muy cómodo te asesorarán para poder formalizar tu negocio. También puedes acudir a las distintas oficinas del Servicio de Constitución de Empresas del Ministerio de la Producción, que además tiene un servicio en línea (pero te aconsejamos hacerlo presencialmente ya que hemos detectado problemas de desconexión en sus servicios en línea).</p>";
                      }    
                //FIN      

                //EXPERIENCIA EN LOS NEGOCIOS
                    /*
                    `635296X1X10` = "¿Tienes experiencia en iniciar otros negocios o emprendimientos?"
                    */  
                      if($data['635296X1X10']=='Y'){
                        $experienciaNegocios = "Sin duda tu experiencia en iniciar negocios previos es un factor decisivo en tu propia confianza. Creo que estás listo para la acción.";
                      }
                      else{
                        $experienciaNegocios = "Tu confianza es inusualmente alta pero vemos que no tienes experiencia en iniciar negocios. Sería prudente que puedas hacer una lista de las actividades que debes realizar como parte de tu 
                      esfuerzo de emprendimiento y que la clasifiques en función del tiempo y de la dificultad de cada una. Además te sugerimos entrevistarte con un emprendedor exitoso y centrar tu conversación en 
                      las acciones más difíciles y que más tiempo toman de tu lista. Es mejor estar seguro que esa confianza que tienes en ti mismo no sea sobre valorada. Ánimo y adelante.";
                      }
                //FIN

                //EXPERIENCIA DE TRABAJO
                    /*
                    `635296X1X8`  = "¿Tienes experiencia de trabajo en el área de tu emprendimiento?"
                    */  
                      if($data['635296X1X8']=='Y' && $data['635296X1X10']=='N'){
                        $experienciaTrabajo = "Como tienes experiencia en el área de tu emprendimiento esta sería una doble buena señal.";
                      }
                      else{
                        $experienciaTrabajo = "Tu confianza es alta pero vemos que no tienes experiencia específica en el negocio que quieres iniciar. Sería prudente que puedas hacer una lista de las actividades que debes realizar como parte de tu esfuerzo de emprendimiento y que la clasifiques en función del tiempo y de la dificultad de cada una. Además te sugerimos entrevistarte con un emprendedor exitoso y centrar tu conversación en las acciones más difíciles y que 
                      más tiempo toman de tu lista. Es mejor estar seguro que esa confianza que tienes en ti mismo no sea sobre valorada. Ánimo y adelante.";
                      }
                //FIN

                //ETAPA:   
                      /* `635296X2X20` = "Seleccione una de las siguientes opciones"
                        A1 = Pensando  
                        A2 = Creando 
                        A3 = Equilibrando 
                        A4 = Consolidando 
                        A5 = Transformando
                      */
                      if($data['635296X2X20']==2){
                        $finaldata = "Recuerda que el tema de emprendedores está de moda. Busca información y noticas que puedan servirte de ayuda, mantente informado. Estimular tu mente te ayudará a mejorar tu capacidad mental para llevar adelante tu emprendimiento. Además podrás detectar a tiempo a oportunidades como financiamientos o concursos. Formar parte de una red de emprendedores también es una buena idea. Allí se comparten experiencias y resulta ser un ambiente muy alentador para quienes están iniciando un negocio. ";
                      }
                      elseif($data['635296X2X20']>2 && $data['635296X2X20']<5){
                        $finaldata = "En la etapa en la que se encuentra tu negocio necesitas rodearte de personas con experiencia y otros emprendedores exitosos. Formar parte de una red de emprendedores es una buena idea, allí se comparten experiencias y resulta ser un ambiente muy alentador para quienes están iniciando un negocio. Recuerda que hay organizaciones que te ayudan a profesionalizar tu negocio. También puedes capacitarte, la ventaja de estudiar es que se vuelve muy estimulante cuando comienza ver las oportunidades de aplicar todo lo que te enseñan así como el compartir tus experiencias con otras personas.";
                      }
                      elseif($data['635296X2X20']==5){
                        $finaldata = "En la fase del negocio en que estás lo más importante el poder llevar esta proactividad a los equipos que están impulsando ese crecimiento que deseas en tu negocio. Tú y tu equipo necesitan conocer otras experiencias de organizaciones líderes que están innovando o buscando nuevos mercado. Formar parte de una red de emprendedores es una buena idea, allí se comparten experiencias y resulta ser un ambiente muy alentador para quienes están iniciando un negocio. Recuerda que hay algunas organizaciones que buscan promover organizaciones como la tuya, trata de acceder a alguna de ellas, si estás en el Perú, te aconsejamos buscar a “Los Ynnovadores”. Y también hay muchas organizaciones de primer nivel que trabajan con emprendedores sociales, búscalos y contacta con ellos. Tienes mucho que ofrecer y también mucho que ganar.";
                      }
                //FIN

                //ARRAY Autoeficacia Emprendedora
                    /*
                    `635296X3X28SQ001` = "Desarrollar y mantener relaciones favorables con potenciales inversores."
                    `635296X3X28SQ002` = "Reconocer nuevas oportunidades en el mercado para nuevos productos y servicios."
                    `635296X3X28SQ003` = "Reclutar y entrenar a los empleados claves."
                    `635296X3X28SQ004` = "Expresar la visión y valores de la organización."
                    `635296X3X41SQ005` = "Descubrir nuevas formas para mejorar los productos existentes."
                    `635296X3X41SQ006` = "Desarrollar relaciones con personas importantes que están vinculadas a fuentes de capital."
                    `635296X3X41SQ007` = "Diseñar productos que resuelvan problemas actuales."
                    `635296X3X41SQ008` = "Crear un entorno de trabajo que permita a las personas ser más su propio jefe."
                    `635296X3X48SQ009` = "Determinar si el negocio irá bien."
                    `635296X3X48SQ010` = "Crear productos que satisfagan las necesidades no cubiertas de los clientes."
                    `635296X3X48SQ011` = "Comercializar los productos de manera adecuada."
                    `635296X3X48SQ012` = "Formar una asociación o alianza con otros."
                    */  
                    $varAutoeficacia = array(
                      $data['635296X3X28SQ001'], $data['635296X3X28SQ002'], $data['635296X3X28SQ003'], $data['635296X3X28SQ004'], $data['635296X3X41SQ005'], $data['635296X3X41SQ006'], 
                      $data['635296X3X41SQ007'], $data['635296X3X41SQ008'], $data['635296X3X48SQ009'], $data['635296X3X48SQ010'], $data['635296X3X48SQ011'], $data['635296X3X48SQ012']);
                      $resultadoAutoeficacia = contador($varAutoeficacia, 12);
                          //Autoeficacia Emprendedora
                          /*
                          `635296X1X10` = "¿Tienes experiencia en iniciar otros negocios o emprendimientos?"
                          `635296X1X8`  = "¿Tienes experiencia de trabajo en el área de tu emprendimiento?"
                          */
                                      if ($resultadoAutoeficacia>=71 && $resultadoAutoeficacia<=84)
                                      {
                                        $AutoeficaciaEmprendedora= "Es poco común tener una confianza tan alta en tus capacidades emprendedoras."."<br><p>".$experienciaNegocios."</p>";
                                        //COMPLEMENTO  
                                        if($data['635296X1X10']=='Y'){
                                          $complementoAutoeficacia = "<p>Sin duda tu experiencia en iniciar negocios previos es un factor decisivo en tu propia confianza. Creo que estás listo para la acción.</p>"; 
                                        }
                                        elseif($data['635296X1X10']=='N'){
                                          $complementoAutoeficacia = "<p>Tu confianza es inusualmente alta pero vemos que no tienes experiencia en iniciar negocios. Sería prudente que puedas hacer una lista de las actividades que debes realizar como parte de tu esfuerzo de emprendimiento y que la clasifiques en función del tiempo y de la dificultad de cada una. Además te sugerimos entrevistarte con un emprendedor exitoso y centrar tu conversación en las acciones más difíciles y que más tiempo toman de tu lista. Es mejor estar seguro que esa confianza que tienes en ti mismo no sea sobre valorada. Ánimo y adelante.</p>"; 
                                        }
                                      }
                                      elseif ($resultadoAutoeficacia>=57 && $resultadoAutoeficacia<=70)
                                      {
                                        $AutoeficaciaEmprendedora= "Tienes un nivel alto de confianza en tus capacidades de emprendimiento. Esto es una buena señal pero no te garantiza el éxito."."<br><p>".$experienciaTrabajo."</p>";
                                        //COMPLEMENTO  
                                          if($data['635296X1X8']=='Y')
                                          {
                                            $complementoAutoeficacia = "<p>Como tienes experiencia en el área de tu emprendimiento esta sería una doble buena señal.</p>"; 
                                          }
                                          elseif($data['635296X1X8']=='N' and $data['635296X1X10']=='N')
                                          {
                                            $complementoAutoeficacia = "<p>Tu confianza es alta pero vemos que no tienes experiencia específica en el negocio que quieres iniciar. Sería prudente que puedas hacer una lista de las actividades que debes realizar como parte de tu esfuerzo de emprendimiento y que la clasifiques en función del tiempo y de la dificultad de cada una. Además te sugerimos entrevistarte con un emprendedor exitoso y centrar tu conversación en las acciones más difíciles y que más tiempo toman de tu lista. Es mejor estar seguro que esa confianza que tienes en ti mismo no sea sobre valorada. Ánimo y adelante.</p>"; 
                                          }
                                      }
                                      elseif ($resultadoAutoeficacia>=29 && $resultadoAutoeficacia<=56)
                                      {
                                        $AutoeficaciaEmprendedora= "Tu puntaje muestra una confianza media en tus capacidades emprendedoras.";
                                        //COMPLEMENTO  
                                          if($data['635296X1X8']=='Y' or $data['635296X1X10'=='Y'])
                                          {
                                             $complementoAutoeficacia = "<p>Sin embargo nos has mencionado que tienes experiencia. Normalmente la experiencia te permite hacer una valoración más real de tus capacidades y de las dificultades que hay que enfrentar. </p>
                                              <p>Pero recuerda algo: ¿por qué son importantes tus propias creencias sobre tus capacidades emprendedoras? La autoeficacia es una creencia no es una realidad. Lo importante radica en que las personas que se creen capaces se esfuerzan más y son más optimistas cuando deben de enfrentar obstáculos. Por el contrario con una confianza baja tienen a abandonar con mayor rapidez el trabajo iniciado. </p>
                                              <p>Ahora bien, también es posible que evalúas como muy difícil las tareas que debes de cumplir. Sería muy oportuno realizar un plan de acción personal con metas, objetivos y un cronograma; o de ser necesario busca capacitarte en aquellas tareas que consideras son más complicadas y por tanto no te sientes tan seguro. </p>
                                              <p>Ánimo, las respuestas están dentro tuyo. Solo debes tener un poco más de confianza.</p>"; 
                                          }
                                          elseif($data['635296X1X8']=='N' or $data['635296X1X10']=='N')
                                          {
                                             $complementoAutoeficacia = "
                                              <p>Es entendible que tu confianza no sea más alta debido a que no tienes experiencia en el campo de tu emprendimiento ni en iniciar otros negocios o emprendimientos sociales. </p>
                                              <p>Quizá sea oportuno que puedas capacitarte para mejorar la confianza en tus capacidades y para conocer a otros emprendedores exitosos que te permitan tener ideas más claras del camino a seguir.</p>"; 
                                          }
                                      }
                                      elseif ($resultadoAutoeficacia>=21 && $resultadoAutoeficacia<=28)
                                      {
                                          $AutoeficaciaEmprendedora= "
                                          <p>Vemos que tienes baja confianza en tu capacidad emprendedora. Esta no es una buena condición para iniciar un emprendimiento ya que podrías desmoralizarte muy rápido al creer que no eres 
                                          capaz. La autoeficacia es una creencia no es una realidad. Lo importante radica en que las personas que se creen capaces se esfuerzan más y son más optimistas cuando deben de enfrentar obstáculos. Por el contrario con una confianza baja tienen a abandonar con mayor rapidez el trabajo iniciado. 
                                          </p>

                                          <p>
                                          Quizá sea oportuno que puedas capacitarte para mejorar la confianza en tus capacidades y para conocer a otros emprendedores exitosos que te permitan tener ideas más claras del camino a seguir.
                                          </p>";
                                      }
                                      elseif ($resultadoAutoeficacia>=0 && $resultadoAutoeficacia<=20)
                                      {
                                          $AutoeficaciaEmprendedora= "
                                          <p>Tu confianza es inusualmente baja. Es probable que no hayas contestado la encuesta correctamente. Te sugerimos revises tu encuesta y de ser necesario vuélvela a responder.
                                          </p>

                                          <p>Si tus datos son correctos, entonces te sugerimos capacitarte para mejorar la confianza en tus capacidades. Además busca conocer a otros emprendedores exitosos que te permitan tener ideas más claras del camino a seguir.
                                          </p>";
                                      }
                //FIN
                                        
                //ARRAY Proactividad
                                  $varProactividad = array(
                                      $data['635296X5X65SQ001'], $data['635296X5X65SQ002'], $data['635296X5X65SQ003'], $data['635296X5X65SQ004'], $data['635296X5X65SQ005'], 
                                      $data['635296X5X65SQ006'], $data['635296X5X65SQ007'], $data['635296X5X65SQ008'], $data['635296X5X65SQ009'], $data['635296X5X65SQ010']);
                                  $resultadoProactividad = contador($varProactividad, 10); 
                                  //Proactividad
                                      $Proactividad_data="
                                        La proactividad se refiere al hecho de que tomas iniciativa y comienzas a ejecutar acciones por tu propia cuenta. Esto te permite hacer un uso muy efectivo de tu tiempo, encontrar oportunidades y conocer a personas que te serán de ayuda durante tu emprendimiento. La proactividad también te permite compartir el placer y los sentimientos positivos que sientes al iniciar tu negocio, esto ayuda a conectar mejor con la gente, a contagiar tu entusiasmo y a ser mejor valorado por los demás.  Muchos expertos señalan que la proactividad te permite tener conexiones con personas que realmente nos sorprenden positivamente porque eran “justo a quien necesitábamos”. Hemos evaluado tu resultado personal para esta característica psicológica determinante en los emprendedores.";
                                      
                                      if ($resultadoProactividad>=61 && $resultadoProactividad<=70)
                                      {
                                          $Proactividad= "
                                          <p>Felicitaciones, tienes un nivel de proactividad muy alto. Esto te permitirá encontrar atrapar muchas oportunidades además de encontrar otras nuevas. Vas a necesitar un buen sistema para el manejo de tus contactos, citas y cuaderno de notas. En tu caso la organización y focalización son claves o tu proactividad podría causarte dificultades. </p>
                                          <p>Lo importante es mantener tu nivel de entusiasmos. Para las personas muy activas, como pareces ser tu, es importante evitar desenfocarte y comenzar a hacer actividades que te distraen de tus objetivos principales. Te sugerimos llevar una muy buena agenda y fijar tus objetivos para cada día.</p>";
                                      }
                                      elseif ($resultadoProactividad>=49 && $resultadoProactividad<=60)
                                      {
                                          $Proactividad= "
                                          <p>Tu nivel de proactividad es alto y debes de mantenerlo así, tu entusiasmo debe incluso aumentar conforme veas los resultados de tu esfuerzo.</p>
                                          <p>Potencia tu proactividad con un buen sistema para el manejo de tus contactos, citas y cuaderno de notas. La organización será tu mejor aliado.</p>";
                                      }
                                      elseif ($resultadoProactividad>=25 && $resultadoProactividad<=48)
                                      {
                                          $Proactividad= "
                                          <p>Tu nivel de proactividad es medio. Sin embargo necesitas elevarlo. Iniciar un emprendimiento requiere de mucha acción y entusiasmo para contactar con personas y realizar actividades.</p>
                                          <p>Te sugerimos tomarte 15 minutos al iniciar tu mañana identificar las acciones que debes y puedes ejecutar durante el día. Es importante que una vez por semana hagas una evaluación de tus acciones respecto a los objetivos centrales de tu emprendimiento.</p>";
                                      }
                                      elseif ($resultadoProactividad>=19 && $resultadoProactividad<=24)
                                      {
                                          $Proactividad= " 
                                          <p>Tu nivel de proactividad es bajo. Iniciar un emprendimiento requiere de mucha acción y entusiasmo para contactar con personas y realizar actividades.</p>
                                          <p>Te sugerimos tomarte 15 minutos al iniciar tu mañana identificar las acciones que debes y puedes ejecutar durante el día. Es importante que emprendimiento.</p>";
                                      }
                                      elseif ($resultadoProactividad>=0 && $resultadoProactividad<=18)
                                      {
                                          $Proactividad= "
                                          <p>Tu nivel de proactividad es inusualmente bajo. Es probable que no hayas contestado la encuesta correctamente. Te sugerimos revises tu encuesta y de ser necesario vuélvela a responder.</p>
                                          <p>Si tus datos son correctos, entonces te sugerimos evaluar el nivel de iniciativa y acción que debes de tener. Iniciar un emprendimiento requiere de mucha acción y entusiasmo para contactar con personas y realizar actividades.</p>
                                          <p>Es importante que te alimentes bien, quizá sea bueno iniciar algún deporte o actividad física que te permita tener mayor actividad corporal y mental.</p>";
                                      }
                //FIN

                //ARRAY SATISFACCION DE VIDA
                  /*
                  `635296X6X84SQ001` = "Voy a seguir con este negocio o empezar otro nuevo."
                  `635296X6X84SQ002` = "Quiero buscar trabajo en una empresa u organización."
                  `635296X6X84SQ003` = "Me he complicado la vida innecesariamente con este negocio."
                  `635296X6X84SQ004` = "Prefiero ser dueño de mi propio negocio."
                  `635296X6X84SQ006` = "Quisiera dejar este tema del emprendimiento."
                  */
                    $varSatisfaccion = array(
                    $data['635296X6X84SQ001'], $data['635296X6X84SQ002'], $data['635296X6X84SQ003'], $data['635296X6X84SQ004'], $data['635296X6X84SQ006']);
                    $resultadoSatisfaccion = contador($varSatisfaccion, 5);      
                //FIN

                //ARRRAY SATISFACCION CON EL EMPRENDIMIENTO
                  $varSatisfaccionEmprendimiento = array(
                        $data['635296X5X65SQ001'], $data['635296X5X65SQ002'], $data['635296X5X65SQ003'], $data['635296X5X65SQ004'], $data['635296X5X65SQ005'], 
                        $data['635296X5X65SQ006'], $data['635296X5X65SQ007'], $data['635296X5X65SQ008'], $data['635296X5X65SQ009'], $data['635296X5X65SQ010']);
                        $resultadoSatisfaccionEmprende = contador($varProactividad, 1);  
                  
                  //EMPRENDIMIENTO ALTO & "VIDA ALTO-BAJO"                    
                  if ($resultadoSatisfaccionEmprende>=6 && $resultadoSatisfaccionEmprende<=7 and $resultadoSatisfaccion>=25 && $resultadoSatisfaccion<=35)
                    {
                      $SatisfaccionEmprendimiento= "<p>Tu condición psicológica en esta dimensión es estupenda. La satisfacción que experimentas es un gran capital a tu favor. Al parecer tienes una vida satisfactoria que también te ayuda a impulsar tu negocio pero a la vez a darte el necesario equilibrio personal que todos necesitamos.</p>";
                    }
                    elseif ($resultadoSatisfaccionEmprende>=6 && $resultadoSatisfaccionEmprende<=7 and $resultadoSatisfaccion>=13 && $resultadoSatisfaccion<=24)
                    {
                      $SatisfaccionEmprendimiento= "<p>Parece que realmente has logrado encontrar lo que te apasiona. Este es el momento de llevar esa energía a tu vida personal. Recuerda que es necesario lograr un equilibrio personal en todos los aspectos de tu vida.</p>";
                    }
                    elseif ($resultadoSatisfaccionEmprende>=6 && $resultadoSatisfaccionEmprende<=7 and $resultadoSatisfaccion>=5 && $resultadoSatisfaccion<=12)
                    {
                      $SatisfaccionEmprendimiento= "<p>Tu trabajo te apasiona pero tu vida personal parece estar atravesando un mal momento. Aprovecha la energía y ánimo que tu emprendimiento te está dando. Pero recuerda que difícilmente podrás mantener un buen ánimo si tu vida personal no logra un equilibrio. Será oportuno que vayas dándole algo de tiempo a tu vida personal. Evalúa la posibilidad de tener un apoyo psicológico que te permita superar este desánimo en tu vida personal.</p>";
                    }   

                  //EMPRENDIMIENTO MEDIO & "VIDA ALTO-BAJO"                    
                  if ($resultadoSatisfaccionEmprende>=4 && $resultadoSatisfaccionEmprende<=5 and $resultadoSatisfaccion>=25 && $resultadoSatisfaccion<=35)
                    {
                      $SatisfaccionEmprendimiento= "<p>Al parecer te gusta lo que has iniciado pero debes de preguntarte si es realmente lo que quieres para ti. Por lo pronto te sugerimos fijarte alguna meta importante dentro de los próximos tres meses y evaluar al llegar a este hito si esto es realmente lo que quieres hacer. Pero primero esfuérzate para que tu evaluación tenga mayor materialidad y sea menos una especulación sobre ti mismo y tus capacidades.</p>";
                    }
                    elseif ($resultadoSatisfaccionEmprende>=4 && $resultadoSatisfaccionEmprende<=5 and $resultadoSatisfaccion>=13 && $resultadoSatisfaccion<=24)
                    {
                      $SatisfaccionEmprendimiento= "<p>Estas en un buen momento para que este emprendimiento te ayude a reflexionar sobre otras cosas que buscas en la vida. Tu emprendimiento puede llegar a convertirse en un signo distintivito de tu persona, en parte de tu identidad de un modo positivo. Te sugerimos fijarte alguna meta importante dentro de los próximos tres meses y volver luego sobre esta reflexión. Pero primero esfuérzate para que tu evaluación tenga mayor materialidad y sea menos una especulación sobre ti mismo y tus capacidades.</p>";
                    }
                    elseif ($resultadoSatisfaccionEmprende>=4 && $resultadoSatisfaccionEmprende<=5 and $resultadoSatisfaccion>=5 && $resultadoSatisfaccion<=12)
                    {
                      $SatisfaccionEmprendimiento= "<p>Estas en un buen momento para que este emprendimiento te ayude a reflexionar sobre otras cosas que buscas en la vida. Tu emprendimiento puede llegar a convertirse en un signo distintivito de tu persona, en parte de tu identidad de un modo positivo. Te sugerimos fijarte alguna meta importante dentro de los próximos tres meses y volver luego sobre esta reflexión. Pero primero esfuérzate para que tu evaluación tenga mayor materialidad y sea menos una especulación sobre ti mismo y tus capacidades.</p>";
                    }    

                  //EMPRENDIMIENTO MEDIO & "VIDA ALTO-BAJO"                    
                    if($resultadoSatisfaccionEmprende>=1 && $resultadoSatisfaccionEmprende<=3 and $resultadoSatisfaccion>=25 && $resultadoSatisfaccion<=30)
                    {
                      $SatisfaccionEmprendimiento= "<p>Tu insatisfacción con el emprendimiento que has iniciado nos hace pensar que estás a punto de dejarlo. Quizá tengas las cosas claras y esta sea una buena decisión para ti, pero si tienes dudas te sugerimos buscar a algunas personas que admiras y conversar con ellas para formarte una mejor opinión antes de tomar una decisión.</p>";
                    }
                    elseif ($resultadoSatisfaccionEmprende>=1 && $resultadoSatisfaccionEmprende<=3 and $resultadoSatisfaccion>=13 && $resultadoSatisfaccion<=24)
                    {
                      $SatisfaccionEmprendimiento= "<p>Tu insatisfacción con el emprendimiento que has iniciado nos hace pensar que estás a punto de dejarlo. Quizá tengas las cosas claras y esta sea una buena decisión para ti, pero si tienes dudas te sugerimos buscar a algunas personas que admiras y conversar con ellas para formarte una mejor opinión antes de tomar una decisión. Por otro lado, te sugerimos también darte un tiempo y evaluar bien las opciones que tienes. Siempre hay alternativas delante nuestro aunque a veces nos resulte difícil verlas.</p>";
                    }
                    elseif ($resultadoSatisfaccionEmprende>=1 && $resultadoSatisfaccionEmprende<=3 and $resultadoSatisfaccion>=5 && $resultadoSatisfaccion<=12)
                    {
                      $SatisfaccionEmprendimiento= "<p>Tu insatisfacción tanto en tu emprendimiento como en la vida personal nos llevan a pensar que puedes necesitar ayuda. Ahora bien, puede ser que estés pasando un una situación temporal muy complicada, si esto es así date un par de días ya que necesitas una mayor distancia psicológica de las cosas: date tiempo para ti y regresa luego a tus actividades. Si no se trata de una situación temporal te sugerimos acudir a un psicólogo y evaluar qué es lo que realmente quieres hacer en la vida y buscar lo que te apasiona. Quizá ha llegado el momento de hacer un cambio importante en tu vida.</p>";
                    }                     
                //FIN
                    
                      
                //SATISFACCION CON EL EMPRENDIMIENTO
                  $Content_satisfaccionEmprendimiento = "
                  <p>Lograr hacer lo que realmente te apasiona es un factor determinante en la persistencia, resilencia (persistir ante la adversidad) y finalmente en el éxito de tu emprendimiento. La anterior es la razón por la cual hemos incluido una escala de satisfacción con tu vida y unas preguntas de satisfacción con tu emprendimiento.</p>
                  <p>Los estudios globales más importantes han demostrado que los emprendedores por oportunidad tienden a estar más satisfechos con su vida. 
                  En tu caso eres un emprendedor por ".$data['635296X2X12']." 
                  </p>
                  ";
                //FIN         

                //DEDICARSE TOTALMENTE AL EMPRENDIMIENO
                  if($data['635296X2X20']=='A1' or $data['635296X2X20']=='A2' and $data['635296X4X2361_11']==1) 
                  {
                    $dedicarse_totalmente = "Esta duda es muy común entre los emprendedores que recién están comenzando. Entendemos que te sientes inseguro porque no sabes si lograrás generar los ingresos que necesitas para satisfacer tus necesidades. Es por ello que es muy importante contar con un buen plan de negocios. Ahora, si bien cada caso es único, la regla es que en algún momento debes de dar el salto y dedicarte a tu emprendimiento al 100%. Un emprendimiento importante requiere tiempo, dedicación y por tanto mucho de tu energía. Es muy difícil que logres despegar si tienes que atender dos o más actividades. Lo que está en juego aquí es tu percepción sobre la inseguridad de  generar los ingresos que necesitas.  Pues bien, seguridad total no vas a tener, parte de ser un emprendedor es correr riesgos. Está bien que trates de minimizarlo pero en algún momento tienes que tu mismo comprar tu sueño. ";
                  }
                  elseif($data['635296X2X20']=='A3' or $data['635296X2X20']=='A4' or $data['635296X2X20']=='A5' and $data['635296X4X2361_11']==1) 
                  {
                    $dedicarse_totalmente = "Nos llama la atención que tengas dudas de dedicarte al 100% a tu emprendimiento y ya estés en una etapa con muchos avances. Esta situación nos hace pensar en varias preguntas ¿cómo lograste avanzar con tu emprendimiento dedicándole solo parte de tu tiempo? ¿Quizá hay una persona clave que está haciendo el trabajo por ti? En este último caso debes de pensar muy bien cómo es la relación de negocios que tienes con esa persona y cómo volverla una fortaleza. También nos llama la atención el modelo de negocio que tienes: ¡un modelo que requiere solo tiempo parcial tuyo!";
                  }
                //FIN    

                //PORQUE TE DEMORAS TANTO CARAJO !
                  if($data['635296X6X82SQ001']=='A6' or $data['635296X6X82SQ001']=='A7')
                  {
                      $porquete_demoras= "
                        <tr>
                          <td>Si el negocio está caminando ¿Por qué te demoras tanto en tomar esta decisión?
                          </td>
                        </tr> ";
                  }
                  elseif($data['635296X6X82SQ001']=='A4' or $data['635296X6X82SQ001']=='A5')
                  {
                      $porquete_demoras= "
                        <tr>
                          <td>Tu satisfacción con el negocio es media ¿Quizá ha llegado el momento de dar el salto? Será muy duro quedarse con las dudas de que te faltó hacer algo o dedicarle más tiempo. Si has invertido mucho tiempo y esfuerzo pues dale un poco más de energía, tu emprendimiento la necesita.
                          </td>
                        </tr> ";
                  }
                  elseif($data['635296X6X82SQ001']=='A1' or $data['635296X6X82SQ001']=='A2' or $data['635296X6X82SQ001']=='A3')
                  {
                      $porquete_demoras= "
                        <tr>
                          <td>Los resultados de tu emprendimiento no te resultan satisfactorios. Solo hay dos opciones, o realmente el negocio no logra alcanzar tus expectativas y por tanto el modelo de negocio que tienes debe ser replanteado.; o el tiempo que le has dedicado no ha permitido que tu negocio prospere.  En este segundo caso debes de reflexionar acerca de tu vocación emprendedora y si es lo que realmente quieres para tu vida. Si tu reflexión es positiva respecto al deseo de ser emprendedor pues ya sabes que esta actividad requiere no solo entusiasmos sino tiempo: tu tiempo.
                          </td>
                        </tr> ";
                  }
                //FIN  
                
                //LA VIDA ES VENDER, ENTONCES PROSTITUYETE !
                  if($data['635296X4X2362_11']=='1')
                  {
                      $vida_vender= "
                      <h3 style='color:#F00; font-weight:bold'>La vida es vender</h3>
                        <table>
                          <tr>
                            <td>Cualquier emprendimiento sea de un proyecto típicamente comercial o social requiere de venta. Para muchos expertos la venta lo es todo. Suele suceder que nuestro emprendimiento se basa en un producto o servicio muy bueno e innovador, pero no se vende solo. Nada se vende solo. Requerimos de un equipo o una estructura de comercialización, mejor si es de ambos. Es muy importante que desarrolles tu plan marketing y de comercialización. 
                                                  Felizmente caminos para mejorar este problema de ventas hay varios. Pero primero debes de definir muy bien cuál es el canal de comercialización que mejor se ajusta a tu producto, sea venta directa, consignación, por internet o cualquier otro. Recuerda que una estrategia multicanal resulta muy demandante. Piensa que quizá sea oportuno identificar un experto que pueda ayudarte a desarrollar un plan de comercialización. Si bien cuesta dinero puede ayudarte a ganar mucho tiempo y a evitar errores innecesarios.
                                                  Luego de determinar tus canales de venta debes de definir si te conviene tener un subordinado dedicado a esto o un socio. Esta es una decisión difícil. 
                            </td>
                          </tr>
                                              
                          <tr>
                            <td>  
                            <table width='700' border='0'>
                                <tr>
                                  <td width='163' align='center' valign='top'><strong>Solución</strong></td>
                                  <td width='211' align='center' valign='top'><strong>Principal  ventaja </strong></td>
                                  <td width='317' align='center' valign='top'><strong>Principal  desventaja</strong></td>
                                </tr>
                                <tr>
                                  <td align='left' valign='top'>Persona  o equipo de ventas</td>
                                  <td align='left' valign='top'>Tu    mantienes el control del negocio. </p></td>
                                  <td align='left' valign='top'>Debes  estar en condiciones de supervisar su trabajo.</td>
                                </tr>
                                <tr>
                                  <td align='left' valign='top'>Socio  fuerte en ventas</td>
                                  <td align='left' valign='top'>Se  entiende que su fortaleza principal es la venta.</td>
                                  <td align='left' valign='top'>Compartes  el control de tu negocio (esto también puede ser una ventaja).</td>
                                </tr>
                              </table>  
                            </td> 
                          </tr>
                        </table> ";
                  }
                //FIN  

                //NECESITO MAS BILLLETE
                   if($data['635296X2X21SQ001']='Y') 
                   {
                      $Titulo_necesito_billete="Mis ahorros.";
                   }
                   elseif($data['635296X2X21SQ002']='Y') 
                   {
                      $Titulo_necesito_billete="Dinero familiar.";
                   }
                   elseif($data['635296X2X21SQ003']='Y') 
                   {
                      $Titulo_necesito_billete="Dinero de socios.";
                   }
                   elseif($data['635296X2X21SQ004']='Y') 
                   {
                      $Titulo_necesito_billete="Dinero de amigos.";
                   }
                   elseif($data['635296X2X21SQ005']='Y') 
                   {
                      $Titulo_necesito_billete="Préstamo de una entidad financiera.";
                   }
                   elseif($data['635296X2X21SQ006']='Y') 
                   {
                      $Titulo_necesito_billete="¡Todavía no he invertido nada!";
                   }

                   //CUERPO NECESITO BILLETE
                   if($data['635296X2X21SQ005']=='Y')
                   {
                    $texto_necesito_billete = "
                          <p>Luego de ver tus encuesta, nos llama mucho la atención que hayas logrado un préstamo bancario pero todavía te falte dinero para iniciar tu negocio. Esto es un mal síntoma y nos dice mucho de tu sectorísta. O el sectorísta no te dio todo el dinero que necesitabas o fue incapaz de detectar una falla en tu plan de negocio. En cualquier caso será importante acercarte nuevamente a la institución financiera y analizar juntos este tema, procurando tener una solución conjunta. Ten cuidado de acudir a otra institución financiera para repetir el mismo problema y hacer de tu problema uno mayor. Así busca al sectorísta y resuelvan esto juntos. Ten paciencia.</p>";
                   }
                   else{
                    $texto_necesito_billete = "
                          <p>Será muy oportuno que hagas un gráfico de barras identificando qué porcentaje de financiamiento corresponde a cada fuente. Esto te ayudará tener claridad sobre tu situación actual.</p>
                          <p>No tengo temor a equivocarme al decirte que el dinero no es lo más importante para iniciar un emprendimiento. Lo más importante tampoco es una buena idea. Lo clave es una buena idea transformada en un plan de negocios atractivo para cualquier inversionista. Tres claves serán tu estructura de costos, un buen análisis de tu competencia y tu plan de comercialización. Por supuesto, que es muy importante tu capacidad de expresarte con claridad y de saber responder a las distintas dudas que surgirán de tu negocio.</p>
                          <p>He tenido la oportunidad de asistir a muchas presentaciones de emprendedores que tienen por objetivo lograr financiamiento. Y hecho siempre de menos los 3 y los 5 minutos. En tres minutos debes de lograr explicar con claridad en qué consiste tu negocio de forma atractiva; y en cinco minutos cómo es tu modelo de negocio y por qué puede ser exitoso. Cuando han pasado 5 minutos y solo nos quedan dudas… no hay dinero. Si por el contrario la exposición es clara, vas a darle más tiempo y a comenzar a hacer otras preguntas. Esto ya es señal de que vas por buen camino.
                             Tradicionalmente uno tiende a pensar en una institución bancaria, pero recuerda que esta alternativa es solo viable si tienes activos personales suficientes como para aportarlos como garantías que respalden la deuda. Por lo general, los bancos no dan préstamos hasta que un negocio genere flujos de caja positivos, y no les importan tanto el potencial futuro. </p>
                          <p>Una alternativa muy utilizada actualmente es Kickstarter, donde se solicitan donaciones, recompensas o, dentro de poco, se ofrecerán acciones. Si lo que ofreces es atractivo, es posible que miles de pequeñas aportaciones de gente anónima en internet te ayuden a iniciar tu negocio.</p>
                          <p>Recuerda que hay ciertas cartas bajo la manga que ayudan. Así por ejemplo, presentar avances que sustenten las proyecciones financieras del plan de negocios, como cartas de intención de compra, pueden causar una excelente impresión y dar seguridad. </p>
                          <p>Todos estos consejos te servirán para solicitar financiamiento, sea a un ángel, a un familiar o amigos. ¡Prepárate!</p>";
                   }
                //FIN  

                //TABLA DINAMICA - LAPT
                   //VALIDACION DE TEXTO CON IGUALDADES
                   if(
                    $data['635296X4X2361_10']=='1' and $data['635296X4X2361_11']=='1' 
                    or $data['635296X4X2362_10']=='1' and $data['635296X4X2362_11']=='1'
                    or $data['635296X4X2363_10']=='1' and $data['635296X4X2363_11']=='1'
                    or $data['635296X4X2364_10']=='1' and $data['635296X4X2364_11']=='1'
                    or $data['635296X4X2365_10']=='1' and $data['635296X4X2365_11']=='1'
                    or $data['635296X4X2366_10']=='1' and $data['635296X4X2366_11']=='1'
                    or $data['635296X4X2367_10']=='1' and $data['635296X4X2367_11']=='1'
                    or $data['635296X4X2368_10']=='1' and $data['635296X4X2368_11']=='1'
                    )
                   {
                      $mensajemismaOpcion = "
                              <tr>
                                <td>
                                  <h3 style='color:#F00; font-weight:bold'>Problema resistente</h3>
                                  <p>Vaya, nos llama la atención que tienes más de un problema resistente. Es decir, un problema del pasado que lo vives nuevamente en la actualidad. Primero revisa tu encuesta, quizá es solo un error tuyo al momento de llenarla. Pero si no es un error, tu organización tiene un problema importante de falta de aprendizaje.  Sigue leyendo esta sección de tu reporte personalizado para identificar acciones correctivas que puedes tomar.</p>  
                                </td>
                              </tr>";
                   }
                   //FIN

                   /*FILA 01*/ 
                     if($data['635296X4X2361_10']=='1')
                     {
                      $mensajeTablaDinamica1 = "Me siento inseguro de dejar el trabajo (ingreso económico) que tengo para dedicarme totalmente a mi emprendimiento.";
                     }
                     else{
                      $mensajeTablaDinamica1 = "";
                     }
                     if($data['635296X4X2361_11']=='1')
                     {
                      $mensajeTablaDinamica1_false = "Me siento inseguro de dejar el trabajo (ingreso económico) que tengo para dedicarme totalmente a mi emprendimiento.";
                     }
                     else{
                      $mensajeTablaDinamica1_false = "";
                     }
                   //

                   /*FILA 02*/ 
                     if($data['635296X4X2362_10']=='1')
                     {
                      $mensajeTablaDinamica2 = "La falta de ventas está estancando el negocio.";
                     }
                     else{
                      $mensajeTablaDinamica2 = "";
                     }
                     if($data['635296X4X2362_11']=='1')
                     {
                      $mensajeTablaDinamica2_false = "La falta de ventas está estancando el negocio.";
                     }
                     else{
                      $mensajeTablaDinamica2_false = "";
                     }
                   //

                   /*FILA 03*/ 
                     if($data['635296X4X2363_10']=='1')
                     {
                      $mensajeTablaDinamica3 = "Es difícil encontrar personas eficientes a las que poder delegarles trabajo clave.";
                     }
                     else{
                      $mensajeTablaDinamica3 = "";
                     }
                     if($data['635296X4X2363_11']=='1')
                     {
                      $mensajeTablaDinamica3_false = "Es difícil encontrar personas eficientes a las que poder delegarles trabajo clave.";
                     }
                     else{
                      $mensajeTablaDinamica3_false = "";
                     }
                   //

                   /*FILA 04*/ 
                     if($data['635296X4X2364_10']=='1')
                     {
                      $mensajeTablaDinamica4 = "Tengo dificultades para manejar a mis clientes.";
                     }
                     else{
                      $mensajeTablaDinamica4 = "";
                     }
                     if($data['635296X4X2364_11']=='1')
                     {
                      $mensajeTablaDinamica4_false = "Tengo dificultades para manejar a mis clientes.";
                     }
                     else{
                      $mensajeTablaDinamica4_false = "";
                     }
                   //

                   /*FILA 05*/ 
                     if($data['635296X4X2365_10']=='1')
                     {
                      $mensajeTablaDinamica5 = "Me resulta casi imposible encontrar el socio que necesito.";
                     }
                     else{
                      $mensajeTablaDinamica5 = "";
                     }
                     if($data['635296X4X2365_11']=='1')
                     {
                      $mensajeTablaDinamica5_false = "Me resulta casi imposible encontrar el socio que necesito.";
                     }
                     else{
                      $mensajeTablaDinamica5_false = "";
                     }
                   //  

                   /*FILA 06*/ 
                     if($data['635296X4X2366_10']=='1')
                     {
                      $mensajeTablaDinamica6 = "Tengo dudas de lograr la rentabilidad que necesito.";
                     }
                     else{
                      $mensajeTablaDinamica6 = "";
                     }
                     if($data['635296X4X2366_11']=='1')
                     {
                      $mensajeTablaDinamica6_false = "Tengo dudas de lograr la rentabilidad que necesito.";
                     }
                     else{
                      $mensajeTablaDinamica6_false = "";
                     }
                   //

                   /*FILA 07*/ 
                     if($data['635296X4X2367_10']=='1')
                     {
                      $mensajeTablaDinamica7 = "Ofrecer muchos más productos o servicios de los planificados al inicio nos está creando problemas.";
                     }
                     else{
                      $mensajeTablaDinamica7 = "";
                     }
                     if($data['635296X4X2367_11']=='1')
                     {
                      $mensajeTablaDinamica7_false = "Ofrecer muchos más productos o servicios de los planificados al inicio nos está creando problemas.";
                     }
                     else{
                      $mensajeTablaDinamica7_false = "";
                     }
                   //

                   /*FILA 08*/ 
                     if($data['635296X4X2368_10']=='1')
                     {
                      $mensajeTablaDinamica8 = "Tengo dificultades para encontrar financiamiento.";
                     }
                     else{
                      $mensajeTablaDinamica8 = "";
                     }
                     if($data['635296X4X2368_11']=='1')
                     {
                      $mensajeTablaDinamica8_false = "Tengo dificultades para encontrar financiamiento.";
                     }
                     else{
                      $mensajeTablaDinamica8_false = "";
                     }
                   //  
                    if($data['635296X4X2361_12']==NULL or $data['635296X4X2362_12']==NULL or $data['635296X4X2363_12']==NULL or $data['635296X4X2364_12']==NULL or
                      $data['635296X4X2365_12']==NULL or $data['635296X4X2366_12']==NULL or $data['635296X4X2367_12']==NULL or $data['635296X4X2368_12']==NULL)
                     {
                        $tablaDinamica = "
                        <table width='700' border='0' cellspacing='0'>
                          <tr>
                            <td style='border: 1px solid #cdd0d4'><strong>Problemas del Pasado</strong></td>
                            <td style='border: 1px solid #cdd0d4'><strong>Problemas del Presente</strong></td>
                          </tr>
                          
                          
                          <tr>
                            <td>".$mensajeTablaDinamica1."</td>
                            <td style='border-left: 1px solid #cdd0d4'>".$mensajeTablaDinamica1_false."</td>
                          </tr>

                          <tr>
                            <td>".$mensajeTablaDinamica2."</td>
                            <td style='border-left: 1px solid #cdd0d4'>".$mensajeTablaDinamica2_false."</td>
                          </tr>

                          <tr>
                            <td>".$mensajeTablaDinamica3."</td>
                            <td style='border-left: 1px solid #cdd0d4'>".$mensajeTablaDinamica3_false."</td>
                          </tr>

                          <tr>
                            <td>".$mensajeTablaDinamica4."</td>
                            <td style='border-left: 1px solid #cdd0d4'>".$mensajeTablaDinamica4_false."</td>
                          </tr>

                          <tr>
                            <td>".$mensajeTablaDinamica5."</td>
                            <td style='border-left: 1px solid #cdd0d4'>".$mensajeTablaDinamica5_false."</td>
                          </tr>

                          <tr>
                            <td>".$mensajeTablaDinamica6."</td>
                            <td style='border-left: 1px solid #cdd0d4'>".$mensajeTablaDinamica6_false."</td>
                          </tr>

                          <tr>
                            <td>".$mensajeTablaDinamica7."</td>
                            <td style='border-left: 1px solid #cdd0d4'>".$mensajeTablaDinamica7_false."</td>
                          </tr>

                          <tr>
                            <td>".$mensajeTablaDinamica8."</td>
                            <td style='border-left: 1px solid #cdd0d4'>".$mensajeTablaDinamica8_false."</td>
                          </tr>
                        </table>";
                     }
                    else{
                        $tablaDinamica ="";
                     }
                //   
                  

            //CORREO
                  $pfw_header ="From: <emprendedores2015@startupintelligence.org>\r\n";
                  $pfw_header .= "Reply-To: emprendedores2015@startupintelligence.org \r\n";
                  $pfw_header .= "CC: emprendedores2015@startupintelligence.org\r\n";
                  $pfw_header .= "MIME-Version: 1.0\r\n";
                  $pfw_header .= "Content-type: text/html; charset=iso-8859-1\r\n";
                  $pfw_subject="STARTUP";
                  $pfw_email_to=$data['635296X1X6'];

                  $body = 
                  "
                   <p>
                    <strong>
                      <h1><span style='color:#15486c'>StartUp</span><span style='color:red'>Intelligence.org</span></h1>
                    </strong>
                   </p>
                      Lima, ".$dia." ".date('j')." de ".$mes." del ".date('Y')."<br>

                      <p align='justify'><b>".$sexo." ".$data['635296X1X1']. ": </b>
                        Antes que nada quiero agradecerte por tu interés y el tiempo personal que le has dedicado al llenado de la encuesta Emprendedores 2015. Este cuestionario ha sido diseñado para evaluar emprendedores que ya han iniciado la puesta en marcha de su negocio o emprendimiento social y que buscan el crecimiento sostenible de su iniciativa.
                        Esta investigación es posible gracias a la Cátedra de Emprendedores de la Universidad de Salamanca, además de otras instituciones entre las que debo mencionar el Centro de Innovación y Desarrollo Emprendedor (CIDE) de la Universidad Católica del Perú, StartUp Perú del Ministerio de la Producción, Wayra Perú entre otras instituciones más.
                        El reporte explora los siguientes aspectos: Autoeficacia Emprendedora, Proactividad y la Satisfacción. Las investigaciones muestran que todos estos aspectos son claves para determinar el éxito y la continuidad del emprendimiento. La actual encuesta 2015 indaga además sobre los Problemas Severos Recurrentes que hacen que muchos de los emprendimientos fracasen. A partir de todos estos elementos este reporte personalizado te da un perfil con tus resultados y te ofrece una serie de sugerencias para mejorar y poder sortear los distintos problemas que estas afrontando.
                        Gracias nuevamente por tu participación y espero disfrutes al analizar tu reporte. Cualquier consulta o comentario puedes escribirme al correo electrónico Emprendedores2015@startupintelligence.org o a mi correo electrónico personal.
                      </p> 

                      <strong>Atentamente</strong><br>
                      <strong>Manuel Bernales Pacheco</strong>
                      <p>manolo@startupintelligence.org</p>

                   <table width='750' border='0'>
                      <tr>
                          <td>
                          <h1 style='font-size:24px'>Reporte personalizado de la encuesta <strong>Emprendedores 2015</strong></h1>
                          <hr />
                          <h3 style='color:#F00; font-weight:bold'>Ficha de datos básicos</h3>
                          <table width='750' border='0' style='border:solid 1px #333333; padding:10px;'>
                            <tr>
                              <td><strong>Emprendedor:</strong> ".$data['635296X1X1']."</td>
                              <td><strong>Edad: </strong>".$edad." años</td>
                            </tr>
                            <tr>
                              <td><strong>Email:</strong> ".$data['635296X1X6']."</td>
                              <td><strong>Emprendimiento:</strong> ".$data['635296X2X12']."</td>
                            </tr>
                            <tr>
                              <td><strong>Antiguedad en meses:</strong> ".round($data['635296X1X9'],0)."</td>
                              <td><strong>Experiencia previa importante: </strong>".$experiencia."</td>
                            </tr>
                          </table>
                          </td>
                      </tr>
                          
                      <tr>
                          <td>
                          <h3 style='color:#F00; font-weight:bold'>
                            Etapa del emprendimiento</h3>
                            <p>Según tu propia evaluación tu emprendimiento se encuentra en la etapa <strong>".$nameEtapa."</strong></p>
                          <table width='750' border='0'>".$etapa."
                          </table>
                          </td>
                      </tr>
                          
                      <tr>
                          <td>
                          <h3 style='color:#F00; font-weight:bold'>Formalización</h3>
                            <table width='750' border='0'>".$formalEmpresa."      
                            </table>
                          </td>
                      </tr>

                      <tr>
                          <td>
                            <h3 style='color:#F00; font-weight:bold'>Variables Individuales</h3>
                            <h5 style='color:#900; font-weight:bold'>Autoeficacia emprendedora</h5>
                            <table width='750' border='0'>
                              <tr>
                                <td colspan='2' align='left' valign='top'>
                                <strong>Nivel de autoeficacia:</strong> <span style='color:#69C;'>".Porcentaje($resultadoAutoeficacia,84)." %.</span>
                                <p align='justify'>
                                La autoeficacia emprendedora se refiere a las creencias que tienes sobre tus propias capacidades para emprender. Es decir a todas las acciones necesarias para iniciar un negocio: identificar oportunidades del mercado para productos, buscar socios, reclutar gente, etcétera. Todos los estudios demuestra que es muy importante que tengas confianza en ti mismo y mejor aún si esta confianza viene de la mano con experiencia. 
                                </p>
                                
                                <p>
                                  Hemos Evaluado tu resultado personal para esta característica psicológica determinante en los emprendedores.
                                </p>
                                
                                </td>
                              </tr>
                            </table>         
                              
                            <p align='justify'>".$AutoeficaciaEmprendedora." </p>
                            <p align='justify'>".$complementoAutoeficacia." </p><br>
                          </td>
                      </tr> 

                      <tr>
                          <td>
                            <table width='750' border='0'>
                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Proactividad</h5>
                                  <p>".$Proactividad_data."</p>
                                  <strong>Nivel de proactividad:</strong> <span style='color:#69C;'>".Porcentaje($resultadoProactividad,70)." %.</span>
                                  <p>Muchos expertos señalan que la proactividad te permite tener conexiones con personas que realmente nos sorprenden positivamente porque eran “justo a quien necesitábamos”.
                                  </p>

                                  <p>
                                  Hemos evaluado tu resultado personal para esta característica psicológica determinante en los emprendedores.
                                  </p>
                                </td>
                              </tr>

                              <tr>
                                <td align='justify'>".$Proactividad."</td>
                              </tr>
                            </table>

                            <table width='750' border='0'>
                              <tr>
                                <td>
                                <h5 style='color:#900; font-weight:bold'>Satisfacción con la vida</h5>
                                <strong>Nivel de Satisfacción:</strong> <span style='color:#69C;'>".Porcentaje($resultadoSatisfaccion,35)." %.</span>
                                </td>
                              </tr>

                              <tr>
                                <td align='justify'>".$finaldata."</td>
                              </tr>

                            </table>
                          </td>
                      </tr>

                      <tr>
                          <td>
                            <table width='750' border='0'>
                              <tr>
                                <td>
                                  <h3 style='color:#F00; font-weight:bold'>Satisfacción con el emprendimiento y con tu vida</h3>
                                  <p>".$Content_satisfaccionEmprendimiento."</p>
                                  <p>".$SatisfaccionEmprendimiento."</p>
                                  <p>Recuerda que iniciar un emprendimiento también abre muchas oportunidades para impactar positivamente en tu vida personal. Trata de aprovechar al máximo las oportunidades que se te van presentado y de crear momentos de celebración por cada meta que logres alcanzar. Estos momentos de celebración deben ser momentos con los que te premias compartiendo sanamente con los demás, y de preferencia con el equipo que te está acompañando en este esfuerzo. Estás logrando hacer de este aspecto una fortaleza de tu perfil emprendedor.</p>  
                                </td>
                              </tr>
                            </table>
                          </td>
                      </tr>

                      <tr>
                          <td>
                            <table width='750' border='0'>
                              <tr>
                                <td>
                                  <h3 style='color:#F00; font-weight:bold'>Problemas del emprendimiento severos y recurrentes</h3>
                                  <p>La investigación y el trabajo de los centros de incubación han identificado diversos problemas recurrentes que deben de enfrentar los emprendedores. Este camino muchos lo llaman “el valle de la muerte” ya que muchos fracasan en el intento. Sin embargo todas las etapas del emprendimiento plantean nuevos retos a los emprendedores y los equipo que trabajan en estas organizaciones. En esta sección vamos a analizar los problemas que has tenido que atravesar y aquellos con los que todavía estás luchando. Todo esto para ofrecerte algunas sugerencias que esperamos te sean de utilidad y te permitan tener mayor confianza en ti mismo.</p>  
                                  <p>".$ProblemaEmprendimiento."</p>
                                </td>
                              </tr>

                              <tr>
                                <td>".$tablaDinamica."
                                </td>
                              </tr>
                              ".$mensajemismaOpcion."

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Cuando dedicarse totalmente al emprendimiento </h5>
                                  <p>".$dedicarse_totalmente."</p>
                                </td>
                              </tr> 
                              
                              ".$porquete_demoras." 

                              <tr>
                                <td>
                              ".$vida_vender."
                                </td>
                              </tr> 

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Equipo humano </h5>
                                  <p>Para muchos emprendedores es difícil confiar en el trabajo de los demás. Por eso es frecuente que quieran hacer todo ellos solos. El primer paso es asegurarte que no seas tu el problema. Cuando tu emprendimiento crece necesitas de otras personas, y delegar es una actividad fundamental para el desarrollo de tu organización. Si el problema eres tu, haz una lista con las tareas que vas a ir delegando y completa esta lista con un cuadro que indique que es lo que esperas de la persona en un determinado plazo de tiempo: comienza a delegar. 
                                    Ahora bien, cazar al equipo humano que necesitas, entrenarlo, motivarlo y mantenerlo no es tarea fácil. Como otras acciones requieren de tu tiempo y energía. Es muy importante que comiences con un equipo pequeño pero potente. Un equipo pequeño es más fácil de dirigir puede tomar autonomía con mucha mayor celeridad y puedes instalar la cultura que necesitas atendiendo los distintos problemas que surgen. Un equipo grande por el contrario puede desbordar tus capacidades y requerirá una profesionalización de su manejo. 
                                    Recuerda que el proceso de gestión de recursos humanos es un ciclo. Y no importa si estamos hablando de muchas o de pocas personas, el ciclo es igual para organizaciones pequeñas y grandes. Los siguientes son los pasos del ciclo general en la gestión del recurso humano:
                                  </p>
                                  <center>
                                    <img src='http://startupintelligence.org/encuesta/images/gestionPersonal.png'>
                                  </center>

                                  <ol>
                                    <li><strong>Análisis y descripción de puestos </strong>Lo importante es determinar muy bien cuál es la persona que necesitas, y por esto nos referimos a las competencias (conocimientos, habilidades y actitudes) que requieres. Un error común es buscar gente que “hace todo”. Difícilmente un buen vendedor es un buen administrativo. Si esta planificación no está bien hecha todo lo demás va a fracasar.</li>
                                    <li><strong>Atracción y selección </strong>Es importante saber cómo logras llegar a las personas con el potencial que necesitas. Cómo los convocas y escoges. </li>
                                    <li><strong>Capacitación</strong>Lo ideal es siempre contratar a alguien a quien no haya que enseñarle nada, pero esta persona es más cara.  Por lo general al comenzar vas a contratar a personas que requieran cierta capacitación. Debes asegurarte que este proceso es claro y breve en el tiempo. </li>
                                    <li><strong>Evaluación del desempeño </strong>Este proceso es vital para que las personas sepan si lo están haciendo bien y cómo deben de mejorar. Sin evaluación del desempeño no hay norte. Este no es un proceso complicado, en una organización pequeña y parte por la fijación de metas en la cual todos sabes qué esperan de su trabajo.</li>
                                    <li><strong>Remuneraciones y beneficios  </strong>Si una correcta remuneración tus trabajadores pueden desertar. No está mal que un trabajador que ha cumplido su ciclo se vaya, pero si está mal que inviertas en él y no hayas obtenido lo que esperabas. Pese a que nadie está contento con sus ingresos, es importante estar seguro que este factor no está siendo una amenaza para tu organización.</li>
                                  </ol>

                                  <p>Dado que has señalado que este es un problema severo en tu organización, debes de analizar cada uno de los procesos de gestión de las personas y ver dónde estás fallando y establecer las acciones correctivas necesarias. Recuerda que puedes encontrar un experto que te pueda ayudar a mejorar estos procesos. Pero primero has tu análisis y luego busca al experto si lo consideras oportuno.</p>
                                </td>
                              </tr> 

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>El cliente siempre tiene la razón </h5>
                                  <p>Tener dificultades con el cliente evidencia que hay muchas oportunidades de mejorar nuestro producto y servicio. El cliente es la fuente de mejora e innovación más importante.</p>
                                  <p>Vas a necesitar determinar si el un problema del propio producto o servicio, o si por el contrario es un problema de atención. Comencemos por el segundo que es más fácil. Los problemas de atención se solucionan rápidamente con una adecuada capacitación y supervisión de las personas que están frente a nuestros clientes (frontdesk). Quizá tengas que desarrollar un protocolo de atención o para resolver los malestares más frecuentes del cliente. Muchas de las respuestas que necesitas están en tu propio equipo. Será importante capturar la información que ya tienes, que viene de cada uno de los casos de quejas y reclamos, y que la puedas analizar.</p>
                                  <p>Ahora bien, si por el contrario al punto anterior, el problema es del propio producto o servicio vas a necesitar hacer un esfuerzo mayor. Para este caso recomiendo establecer un equipo de calidad. Funciona de forma similar a lo que es un círculo de calidad, cuya información podrás encontrar en cualquier librería o incluso en la internet. El objetivo es convocar a quienes saben el malestar del cliente y a quienes están involucrados en el diseño del producto o servicio. Será muy importante que estas personas puedan tener acceso a información: cantidad de quejas, problemas de producción o de trabajo, etc. Así este equipo podrá abordar el problema e intentar mejorarlo. Para esta estrategia te recomendamos asesorarte con una persona que tenga experiencia en manejar este tipo de equipos. Estas a punto de comenzar a trabajar sobre el corazón de tu producto y servicio y por tanto será importante que también te involucres personalmente en este proceso. Míralo con ánimo, este tipo de estrategias permiten que las mejoras e innovaciones sean certeras.</p>
                                </td>
                              </tr> 

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>El socio </h5>
                                  <p>Si has tenido malas experiencias con tus socios analiza la lista anterior y mira en qué fallaste. Muchas veces tendemos a demonizar a un socio con el cual nos fue mal. Este proceso de generalización esconde problemas que no hemos aprendido a enfrentar. </p>
                                </td>
                              </tr> 

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Autoeficacia emprendedora </h5>
                                  <p>Son las creencias que tenemos acerca de nuestras propias capacidades para realizar un emprendimiento. </p>
                                  <p>Encontrar un socio que se ajuste no solo a las necesidades de tu emprendimiento sino a tu estilo personal es tarea difícil.  Vamos a pensar en la definición del socio en cuatro grandes pasos:</p>
                                  <ol>
                                    <li><strong>Rol del socio: </strong>¿Cuál es el valor agregado que espero de mi futuro socio?</li>
                                    <li><strong>Búsqueda de socio: </strong>Es importante saber cómo logras llegar a las personas con el potencial que necesitas. Cómo los convocas y escoges. </li>
                                    <li><strong>Acuerdos previos: </strong>antes de iniciar la sociedad es importante que definan los temas más sensibles en toda sociedad, si es que ya no lo han hecho. Estos temas están referidos a la toma de decisiones, toma de ganancias, reinversión, nuevos socios y cómo finalizar la sociedad. Es importante que hayas tenido el tiempo de dialogar y entender cuáles son las orientaciones de tu socio. Es imposible prever todas las situaciones posibles y ponerlas en un documento legal. Lo ideal es que haya un espíritu común del emprendimiento.</li>
                                    <li><strong>Hoja de ruta inicial: </strong>Es una excelente oportunidad que el inicio de la sociedad se haga en base a un plan de trabajo conjunto, donde cada uno identifique sus metas, objetivos y acciones como parte de un cronograma de trabajo. Esta hoja de ruta le dará mucha materialidad a la sociedad y permitirá comenzar a crear química entre los socios.</li>
                                  </ol>
                                  <p>Poner en práctica estos cuatro pasos debería ayudarte en el objetivo de establecer una relación societaria positiva y con alto potencial de crecimiento para tu negocio o emprendimiento social. Conocer las expectativas de tu futuro socio es la clave para una buena sociedad.</p>
                                </td>
                              </tr> 

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Rentabilidad</h5>
                                  <p>Las dudas sobre la rentabilidad del negocio deben disiparse con un buen plan de negocio y con la simulación de algunos escenarios. Herramientas y modelos sobre planes de negocio hay muchos y te será fácil encontrar varios ejemplos en internet. Desarrolla el tuyo hasta donde puedas y luego puedes pensar en pedir ayuda para completarlo lo mejor posible. </p>
                                  <p>Respecto a simular escenarios, esto normalmente se hace con una hoja de cálculo. Muchas veces se requiere tener conocimientos y poco avanzados de Excel para hacer una simulación más profesional. Pero con conocimientos básicos puedes comenzar. Si te sugiero buscar a un economista que te ayude, no solo ha manejar la hoja de cálculos, sino a incluir supuestos en los cuáles no estamos acostumbrados a pensar. Aquí es donde el famoso ROI (retorno de la inversión) cobra mucho valor.</p>
                                  <p>Los indicadores de negocio, como el ROI, serán de mucha utilidad para poder valorar tu modelo de negocio. Un especialista podrá decirte además cuán buenos o malos son esos indicadores; y por tanto podrás identificar dónde tu modelo debe de ser mejorado o donde hay que replantearlo.</p>
                                </td>
                              </tr> 

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Desenfocarse</h5>
                                  <p>Esta es una tendencia muy común cuando uno tiene un equipo humano instalado y una infraestructura armada. Esta organización ha sido montada para atender un servicio o producto específico. Sin embargo vemos “capacidades ociosas” y creemos que podemos utilizar esta estructura para ampliar nuestro negocio. Lo que la mayoría de las veces ocasiona esto es que nos desenfoquemos muy tempranamente y que nuestro negocio pierda coherencia y potencia. </p>
                                  <p>Normalmente has tomado esta decisión porque ves que la generación de ingresos no es tan fluida como lo necesitas. Pero lo correcto es evaluar tu modelo de negocio y el diseño de tu organización de acuerdo a tu Plan de Negocios inicial. Sucede que cuando te diversificas, sin haberlo evaluado correctamente, es probable que estés desatendiendo la mejora del producto o servicio que fue la base de tu emprendimiento . Es necesario que identifiques en qué está fallando y qué debes de mejorar para tu plan de negocios. </p>
                                  <p>Recuerda qué es estar enfocado: centrado en una sola idea, concreta y bien definida; innovadora y con una ventaja competitiva sostenible.</p>
                                </td>
                              </tr> 

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Necesito más dinero</h5>
                                  <p>Tu Plan de Negocios debe de decirte cuánto dinero necesitas para poder comenzar. En la mayoría de los casos el dinero siempre viene de nuestros primeros círculos (familia y amigos). Pero antes de comenzar esta última sección veamos tus fuentes de financiamiento actuales:</p>
                                </td>
                              </tr> 

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>".$Titulo_necesito_billete."</h5>
                                  ".$texto_necesito_billete."
                                </td>
                              </tr>













                            </table>
                          </td>
                      </tr>

                      <tr>
                          <td>
                            <table width='750' border='0'>
                              <tr>
                                <td>
                                  <h3 style='color:#F00; font-weight:bold'>Glosario de Términos</h3>
                                  <h5 style='color:#900; font-weight:bold'>Ángel (business angel)</h5>
                                  <p>Es un individuo próspero que provee capital para un start-up, usualmente a cambio de participación accionaria.</p>
                                </td>
                              </tr>

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Autoeficacia emprendedora </h5>
                                  <p>Son las creencias que tenemos acerca de nuestras propias capacidades para realizar un emprendimiento. </p>
                                </td>
                              </tr>  

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Centro de Incubación </h5>
                                  <p>Institución que selecciona propuestas de emprendimiento con el fin de apoyarlas y brindarles un ambiente que les permita crecer rápidamente para convertirse en negocios sostenibles.</p>
                                </td>
                              </tr>

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Círculo de calidad</h5>
                                  <p>Grupo de trabajo que se reúne para buscar soluciones a problemas detectados en sus respectivas áreas de desempeño laboral.</p>
                                </td>
                              </tr>

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Competencias emprendedoras</h5>
                                  <p>Se refiere a los conocimientos, habilidades y actitudes requeridas para realizar un emprendimiento.</p>
                                </td>
                              </tr>

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Emprendimiento Social</h5>
                                  <p>Hace referencia a un tipo de empresa en la que su razón de existir es aliviar un problema social.</p>
                                </td>
                              </tr>

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Kickstarter</h5>
                                  <p>Sistema online para búsqueda de aportes (en inglés) para poner en marcha tu emprendimiento.
                                  https://www.kickstarter.com/
                                  </p>
                                </td>
                              </tr>

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Proactividad </h5>
                                  <p>Actitud ante la vida que implica que tomas decisiones con iniciativa propia.</p>
                                </td>
                              </tr>

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Resilencia</h5>
                                  <p>Capacidad de las personas de sobreponerse a situaciones adversas y seguir adelante con tus objetivos.</p>
                                </td>
                              </tr>

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>ROI</h5>
                                  <p>El retorno sobre la inversión (RSI o ROI, por sus siglas en inglés) es indicador financiero que compara el beneficio o la utilidad obtenida en relación a la inversión realizada.</p>
                                </td>
                              </tr>

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Satisfacción con el emprendimiento </h5>
                                  <p>Indica el grado de satisfacción con el esfuerzo de emprendimiento que has realizado hasta ahora.</p>
                                </td>
                              </tr>

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Satisfacción con la vida </h5>
                                  <p>En este estudio utilizamos la encuesta de Pavot y Diener de 5 ítems.</p>
                                </td>
                              </tr>
                              
                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Star-Up</h5>
                                  <p>Negocio que recién se está iniciando.</p>
                                </td>
                              </tr>

                              <tr>
                                <td>
                                  <h5 style='color:#900; font-weight:bold'>Valle de la muerte</h5>
                                  <p>Expresión del mundo del emprendimiento que se refiere al periodo de iniciar un negocio en el cual los problemas pueden hacer fracasar este emprendimiento.</p>
                                </td>
                              </tr>

                            </table>
                          </td>
                      </tr>

                      <tr>
                          <td>
                            <table width='750' border='0'>
                              <tr>
                                <td>
                                  <h3 style='color:#F00; font-weight:bold'>Bibliografía</h3>
                                  <p>Bandura, A. (1994). Self-Efficacy. In R. (Ed.), Encyclopedia of human behavior (pp. 71-81). New York: Academic Press. </p>
                                  <p>Bernales, M. (2010) Intención emprendedora y factor de migración en estudiantes universitarios del Perú. Tesina, sobresaliente cum laude. Universidad de Salamanca.</p>
                                  <p>Pavot, W., & Diener, E. (1993). Review of the Satisfaction with Life Scale. Psychological Assessment, 5(2), 164-172.</p>
                                  <p>Sánchez, J.C.; Lanero, A.; Yurrebaso, A. (2005). Variables determinantes de la intención emprendedora en el contexto. Revista de Psicología Social Aplicada Vol 15, no 1 , 37-60. </p>
                                  <p>Singer, S.; Amoros, J.E. & Moska D. (2015) Global Entrepreneurship Monitor. 2014 Global Report. Global Entrepreneurship Research Association (GERA) </p>
                                </td>
                              </tr>
                            </table>
                          </td>
                      </tr>

                   </table>
                  ";
                  mail($pfw_email_to,$pfw_subject,$body,$pfw_header);
            //FIN       
        }
      }
   

    $resultCache[$sid][$srid] = $completed;
    return $completed;
    }

    public function exist($srid)
    {
        $sid = self::$sid;
        $exist=false;

        if(Yii::app()->db->schema->getTable($this->tableName())){
            $data=Yii::app()->db->createCommand()
                ->select("id")
                ->from($this->tableName())
                ->where('id=:id', array(':id'=>$srid))
                ->queryRow();
            if($data)
            {
                $exist=true;
            }
        }
        return $exist;
    }
    
    /**
     * Return next id if next response exist in database
     *
     * @param integer $srid : actual save survey id
     * @param boolean $usefilterstate
     *
     * @return integer
     */
    public function next($srid,$usefilterstate=false)
    {
        $sid = self::$sid;
        $next=false;
        if ($usefilterstate && incompleteAnsFilterState() == 'incomplete')
            $wherefilterstate='submitdate IS NULL';
        elseif ($usefilterstate && incompleteAnsFilterState() == 'complete')
            $wherefilterstate='submitdate IS NOT NULL';
        else
            $wherefilterstate='1=1';

        if(Yii::app()->db->schema->getTable($this->tableName())){
            $data=Yii::app()->db->createCommand()
                ->select("id")
                ->from($this->tableName())
                ->where(array('and',$wherefilterstate,'id > :id'), array(':id'=>$srid))
                ->order('id ASC')
                ->queryRow();
            if($data)
            {
                $next=$data['id'];
            }
        }
        return $next;
    }

    /**
     * Return previous id if previous response exist in database
     *
     * @param integer $srid : actual save survey id
     * @param boolean $usefilterstate
     *
     * @return integer
     */
    public function previous($srid,$usefilterstate=false)
    {
        $sid = self::$sid;
        $previous=false;
        if ($usefilterstate && incompleteAnsFilterState() == 'incomplete')
            $wherefilterstate='submitdate IS NULL';
        elseif ($usefilterstate && incompleteAnsFilterState() == 'complete')
            $wherefilterstate='submitdate IS NOT NULL';
        else
            $wherefilterstate='1=1';

        if(Yii::app()->db->schema->getTable($this->tableName())){
            $data=Yii::app()->db->createCommand()
                ->select("id")
                ->from($this->tableName())
                ->where(array('and',$wherefilterstate,'id < :id'), array(':id'=>$srid))
                ->order('id DESC')
                ->queryRow();
            if($data)
            {
                $previous=$data['id'];
            }
        }
        return $previous;
    }
    
    /**
     * Function that returns a timeline of the surveys submissions
     *
     * @param string sType
     * @param string dStart
     * @param string dEnd
     *
     * @access public
     * @return array
     */
    public function timeline($sType, $dStart, $dEnd) 
    {
            
        $sid = self::$sid;        
        $oSurvey=Survey::model()->findByPk($sid);
        if ($oSurvey['datestamp']!='Y') {
               return false;
        }
        else
        {    
            $criteria=new CDbCriteria;
            $criteria->select = 'submitdate';
            $criteria->addCondition('submitdate >= :dstart');
            $criteria->addCondition('submitdate <= :dend');    
            $criteria->order="submitdate";
            
            $criteria->params[':dstart'] = $dStart;
            $criteria->params[':dend'] = $dEnd; 
            $oResult = $this->findAll($criteria);
            
            if($sType=="hour")
                $dFormat = "Y-m-d_G";
            else
                $dFormat = "Y-m-d";
            
            foreach($oResult as $sResult)
            {        
                $aRes[] = date($dFormat,strtotime($sResult['submitdate']));        
            }
                
            return array_count_values($aRes);
        }
    }

}
?>
