<?php

    namespace app\models;
    use \PDO;
    
    if(file_exists(__DIR__."/../../config/server.php")){
        require_once __DIR__."/../../config/server.php";
    }
    class mainModel{
        private $server= DB_SERVER;
        private $db =DB_NAME;
        private $user = DB_USER;
        private $pass =DB_PASS;

        protected function conectar (){
            $conexion = new PDO("mysql:host=".$this->server.";dbname=".$this->db,$this->user, $this->pass);
            $conexion->exec("SET CHARACTER SET UTF-8");
            return $conexion;
        }

        protected function ejecutarConsulta($consulta){
            $sql=$this->conetar()->prepare($consulta);
            $sql->execute();
            return $sql;
        }

        public function limpiarCadena($cadena){
            
            $palabras=["<script>","</script>","<script src","<script type=","SELECT * FROM","SELECT "," SELECT ","DELETE FROM","INSERT INTO","DROP TABLE","DROP DATABASE","TRUNCATE TABLE","SHOW TABLES","SHOW DATABASES","<?php","?>","--","^","<",">","==","=",";","::"];

            $cadena=trim($cadena);
            $cadena=stripcslashes($cadena);

            foreach($palabras as $palabra){
                $cadena = str_ireplace($palabra,"",$cadena);
            }
            $cadena=trim($cadena);
            $cadena=stripcslashes($cadena);

            return $cadena;
        }

        protected function verificarDatos($filtro,$cadena){
            /* [a-zA-Z0-9$@.-]{7,100} */
            if(preg_match("/^".$filtro."$/",$cadena)){
                    return false;
                }else{
                    return true;
            }
        }

        protected function guardarDatos($tabla,$datos){
            $query="INSERT INTO $tabla (";
            $C=0;
            foreach($datos as $clave){
                if($C>=1){
                    $query.=",";
                }
                $query.$clave["campo_nombre"];
                $C++;
            }
            $query.=") VALUES(";
            $C=0;
            foreach($datos as $clave){
                if($C>=1){
                    $query.=",";
                }
                $query.$clave["campo_marcador"];
                $C++;
            }
            $query.=")";
            $sql=$this->conectar()->prepare($query);

            foreach($datos as $clave){
                $sql->bindParam($clave["campo_marcador"],$clave["campo_valor"]);
            }

            $sql->execute();

            return $sql;

        }
       
        public function seleccionarDatos($tipo,$tabla,$campo,$id){
            $tipo=$this->limpiarCadena($tipo);
            $tabla=$this->limpiarCadena($tabla);
            $campo=$this->limpiarCadena($campo);
            $id=$this->limpiarCadena($id);

            if($tipo=="Unico"){
                $sql=$this->conetar()->prepare("SELECT * FROM $tabla where $campo=:ID");
                $sql->bindParam(":ID",$id);
            }elseif($tipo=="Normal"){
                $sql=$this->conetar()->prepare("SELECT $campo FROM $tabla");
            }

            $sql->execute();
            return $sql;

        }

        protected function actualizarDatos($tabla,$datos,$condicion){
            $query="UPDATE $tabla SET ";
            $C=0;
            foreach($datos as $clave){
                if($C>=1){
                    $query.=",";
                }
                $query.$clave["campo_nombre"]."=".$clave["campo_marcador"];
                $C++;
            }
            $query.="WHERE ".$condicion["condicion_campo"]."=".$condicion["condicion_marcador"];

            $sql=$this->conectar->prepare($query);
           
            foreach($datos as $clave){
                $sql->bindParam($clave["campo_marcador"],$clave["campo_valor"]);
            }
            $sql->bindParam($condicion["condicion_marcador"],$condicion["condicion_valor"]);

            $sql->execute();
            return $sql;
        }

        protected function eliminarRegistro($tabla,$campo,$id){
            $sql=$this->conectar()->prepare("DELETE FROM $tabla WHERE $campo =:id");
            $sql->bindParam(":id",$id);
            $sql->execute();
            return $sql;
        }

        protected function paginadorTablas($pagina,$numeroPagina,$url,$botones){
            $tablas='<nav class="pagination is-centered is-rounded" role="navigation" aria-label="pagination">';
            if($paginas<=1){
                $tablas.='
                <a class="pagination-previous is-disabled" disabled >Anterior</a>
	            <ul class="pagination-list">
                ';
            }else{
                $tablas.='
                <a class="pagination-previous" href="'.$url.($pagina-1).'/">Anterior</a>
                <ul class="pagination-list">
                    <li><a class="pagination-link" href="'.$url.'1/">1</a></li>
                    <li><span class="pagination-ellipsis">&hellip;</span></li>
                ';
            }
            $ci=0;
            for($i=$pagina; $i<=$numeroPagina; $i++){
                if($ci>=$botones){
                   break; 
                }
                if($pagina==$i){
                    $tabla.='<li><a class="pagination-link is-current" href="'.$url.$i.'">'.$i.'</a></li>';
                }else{

                }
                $ci++;
            }
                if($pagina==$numeroPagina){
                    $tabla.='
                    </ul>
                    	<a class="pagination-next is-disabled" disabled >Siguiente</a>
                    ';
                }else{
                    $tabla.='
                        <li><span class="pagination-ellipsis">&hellip;</span></li>
                        <li><a class="pagination-link" href="'.$url.$numeroPagina.'/">'.$numeroPagina.'</a></li>
                    </ul>
                    	<a class="pagination-next" href="'.$url.($pagina+1).'/">Siguiente</a>

                    ';
                }
                $tablas.='</nav>';
                return $tabla;

        }
    }