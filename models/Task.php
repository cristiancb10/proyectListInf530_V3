<?php
//requiere los metodos de la Coneection.php 
include_once "Connection.php";
//se crea una clase Task con los metodos de la clase Connection
class Task extends Connection{
    //se declara un atributo $table que se inicializa en task
    private $table = "task";
//funcion para mostrar la tabla de tareas de cada usuario por separado 
//se obtiene el parametro de entrada $user_id obtenido por $_GET['id'] 
    public function userTasks($user_id){
// Conecta a la base de datos con this para acceder a la variable golebal de connection
        $this->connect();
//se declara una variable para usar el mysqli_prepare la consulta SQL USA FOREIGN KEY
//TODAS LAS TAREAS DEL ID DEL USUARIO
        $pre = mysqli_prepare($this->connection,
        "SELECT * from $this->table WHERE id_user = ?");
//se parametriza las variblesque ingresan en este caso solo el id de usuario
        $pre->bind_param('i',$user_id);
//se ejecuta la consulta
        $pre->execute();
//se crea una variable $res donde se guarda la consulta con metodo get_result()
        $res = $pre->get_result();
//se usa la propiedad num_rows del objeto mysqli_prepare que devuelve el numero de 
//filas obtenidas en una consulta
//esta se accede desde el res que es quien tiene el resultado de la consulta
//si es cero las filas obtenidas retorna el mensaje 
        if($res->num_rows == 0 ){
            return null;
            echo "No hay tareas";
        }
//se crea una variable $tasks que guarda lo obtenido que se da cuando se accede a la 
//consulta guardada en $res y se usa el metodo fetch_all que extrae todas la filas 
//y con MYSQLI_ASSOC una constante que indica que es un array asociativo
        $tasks = $res->fetch_all(MYSQLI_ASSOC);
        //retorna $tasks
        return $tasks;
    }

//funcion para seleccionar solo una tarea con el id de la tarea
    public function getTaskById($task_id) {
// Conecta a la base de datos con this para acceder a la variable golebal de connection
        $this->connect();
//Y de igual manera se declara una variable $stmt para usar el objeto mysqli_preprare()
//y en el primer parametro hacer la conexion de la base de datos y en la segunda la 
//consulta  SQL  
        $pre = mysqli_prepare($this->connection, "SELECT * FROM $this->table WHERE id = ?");
//se parametriza la variable osea convierte la variable que fue enviada en el formulario      
        $pre->bind_param('i', $task_id);
//se ejecuta la consulta
        $pre->execute();
//se crea una variable $res donde se guarda la consulta con metodo get_result()       
        $res = $pre->get_result();
//genera un array con la fila encontrada y retorna esta
        return $res->fetch_assoc(); 
    }

    public function delete($id){
    //Se hace la conexion a la base de datos usando el $this para acceder de manera aglobal 
    //a la funcion connect()  
        $this->connect();
    //Y de igual manera se declara una variable $stmt para usar el objeto mysqli_preprare()
    //y en el primer parametro hacer la conexion de la base de datos y en la segunda la 
    //consulta  SQL  
        $stmt = mysqli_prepare($this->connection, "DELETE FROM task WHERE id = ?");
    //se parametriza la variable osea convierte la variable que fue enviada en el formulario
        $stmt ->bind_param("i",$id); 
    //Se declara una variable $result que guardara lo que $stmt ejecute con su propiedad
    //execute, ejecuta la consulta
        $result = $stmt -> execute();
    //se crea una variable $affected_rows para primero:acceder a la propiedad affected_rows
    //que tiene el etributo connection por ello el $this,y lo que hace es devolver el 
    //numero de filas afectadas en la consulta (0 no, 1 si, -1 error)  
        $affected_rows = $this->connection->affected_rows;
    //cierra la consulta preparada con close()
        $stmt->close();
    //retorna TRUE si se modifico filas O FALSE si no se afecto ninguna fila
        return ($affected_rows > 0); 
    }

    public function create($description, $due_day, $id_user){
    //Se accede a la funcion connection de la clase Connection
        $this->connect();
    //se crea una variable $stmt donde se hace la conexion con la base de datos usando
    //this-> connection osea accediendo a los atributos de connection y preparar la consulta
        $stmt = mysqli_prepare($this->connection, 
        "INSERT INTO $this->table (description, due_day, id_user) VALUES (?, ?, ?)");
        //se parametriza las variables osea las convierte en ssi, notar que fecha es string
        $stmt->bind_param("ssi", $description, $due_day, $id_user);
        //se ejecuta la consulta y se guarda en result
        $result = $stmt->execute(); 
        //retorna el result 
        return $result; 
    }

    public function update($id_task){
        //se establece la conexion con la base de datos con this para acceder de 
        //manera global al metodo connect
        $this->connect();
        //se declara la variable $stmt para preparar la consulta mysqli_prepare (2 parametros)
        $stmt = mysqli_prepare($this->connection, 
        "UPDATE $this->table SET description = ? , due_day = ?, updated_at = NOW() WHERE id = ?");
        //parametrizan los valores insertados con lo que deben figurar
        //notar que el id no esta con this porque es un parametro del metodo (variable de entrada)
        $stmt -> bind_param("ssi", $this->description, $this->due_day, $id_task);
        //se ejecuta la consulta
        $res = $stmt ->execute();
        //se cierra la funcion
        $stmt -> close();
        return $res;
    }
    
    public function completedTasks($user_id) {
        //se llama a la funcion connect() para acceder a sus atributos  
        $this->connect();
        //se declara una variable $stmt que usa myswli_prepare para preparar la base de datos
        //y la consulta SQL 
        $stmt = mysqli_prepare($this->connection,
        "SELECT * FROM task WHERE id_user = ? AND completed = 1");
        //se parametriza la variable de entrada cono entero
        $stmt->bind_param("i", $user_id);
        //se ejecura la consulta
        $stmt->execute();
        //se guarda el resultado en la variable $res, para ello accede a la propiedad get_result()
        $res = $stmt->get_result();
        //fetch_all(MYSQLI_ASSOC): extra todas las filas como un array asociativo y lo 
        //guarda en la variable $tasks
        $tasks = $res->fetch_all(MYSQLI_ASSOC);
        //se cierra la ejecucion
        $stmt->close();
        //se retorna lo guardado en $tasks osea el array asociativo
        return $tasks;
    }

    public function pendingTasks($user_id) {
        //se llama a la funcion connect()
        $this->connect();
        //se prepara la consulta con mysqli_prepare tomando en cuenta la conexion a la database
        // y la consulta SQL
        $stmt = mysqli_prepare($this->connection,
        "SELECT * FROM task WHERE id_user = ? AND completed = 0");
        //se parametriza o se convierte el valor recibido en entero
        $stmt->bind_param("i", $user_id);
        //se ejecuta la consulta
        $stmt->execute();
        //se guarda el resultado de consulta del objeto $stmt en la variable creada $res
        //con get_result()
        $res = $stmt->get_result();
        //se guarda en $tasks el resultado por medio de fetch_all que devuelve filas y con
        //MYSQLI_ASSOC en forma de array asociativo
        $tasks = $res->fetch_all(MYSQLI_ASSOC);
        //se cierra la ejecucion
        $stmt->close();
        //retorna el array o valor de $tasks
        return $tasks;
    }
    
    public function todayTasks($user_id) {
        //se llama a la funcion connect()
        $this->connect();
//se prepara la consulta con mysqli_prepare tomando en cuenta la conexion a la database
// y la consulta SQL
        $stmt = mysqli_prepare($this->connection,
        "SELECT * FROM task WHERE id_user = ? AND due_day = CURDATE()");
//se parametriza o se convierte el valor recibido en entero
        $stmt->bind_param("i", $user_id);
//se ejecuta la consulta
        $stmt->execute();
    //se guarda el resultado de consulta del objeto $stmt en la variable creada $res
    //con get_result()
        $res = $stmt->get_result();
//se guarda en $tasks el resultado por medio de fetch_all que devuelve filas y con
//MYSQLI_ASSOC en forma de array asociativo
        $tasks = $res->fetch_all(MYSQLI_ASSOC);
//se cierra la ejecucion
        $stmt->close();
//retorna el array o valor de $tasks   
        return $tasks;
    }

    public function setCompletedStatus($id, $completed) {
    //Se establece la conexio de la base de datos con this y se accede a connect
        $this->connect();
    //se declara la variable $stmt donde se prepara la consulta SQL
        $stmt = mysqli_prepare($this->connection, "UPDATE task SET completed = ? WHERE id = ?");
    //Se parametriza los valores que ingresan en este caso dos enteros 
        $stmt->bind_param("ii", $completed, $id);
    //Se declara la variable $success donde se guarda lo ejecutado la cosulta SQL es decir
    //que retorna a $success si se modifico alguna fila TRUE y sino FALSE
        $success = $stmt->execute();
    //se termina la consulta
        $stmt->close();
    //se retorna el TRUE O FALSE
        return $success;
    }
}
