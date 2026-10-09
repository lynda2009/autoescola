<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once "config.php";

$rota = $_GET["rota"] ?? "";
$metodo = $_SERVER["REQUEST_METHOD"];

try {

    if ($rota === "alunos" && $metodo === "GET") {

        $sql = "SELECT * FROM Alunos ORDER BY id";
        $stmt = $pdo->query($sql);

        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($rota === "alunos/create" && $metodo === "POST") {

        $dados = json_decode(file_get_contents("php://input"), true);

        if (
            empty($dados["nome"]) ||
            empty($dados["email"]) ||
            empty($dados["data_nascimento"])
        ) {
            http_response_code(400);
            echo json_encode([
                "erro" => "Preencha nome, email e data de nascimento."
            ]);
            exit;
        }

        $telefone = $dados["telefone"] ?? null;

        $sql = "INSERT INTO Alunos
                (nome, email, telefone, data_nascimento)
                VALUES (?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $dados["nome"],
            $dados["email"],
            $telefone,
            $dados["data_nascimento"]
        ]);

        http_response_code(201);
        echo json_encode([
            "mensagem" => "Aluno cadastrado com sucesso!",
            "id" => $pdo->lastInsertId()
        ]);
        exit;
    }

    if ($rota === "instrutores" && $metodo === "GET") {

        $sql = "SELECT * FROM Instrutores ORDER BY id";
        $stmt = $pdo->query($sql);

        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($rota === "instrutores/create" && $metodo === "POST") {

        $dados = json_decode(file_get_contents("php://input"), true);

        if (
            empty($dados["nome"]) ||
            empty($dados["email"]) ||
            empty($dados["categoria_cnh"])
        ) {
            http_response_code(400);
            echo json_encode([
                "erro" => "Preencha nome, email e categoria da CNH."
            ]);
            exit;
        }

        $telefone = $dados["telefone"] ?? null;

        $sql = "INSERT INTO Instrutores
                (nome, email, telefone, categoria_cnh)
                VALUES (?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $dados["nome"],
            $dados["email"],
            $telefone,
            $dados["categoria_cnh"]
        ]);

        http_response_code(201);
        echo json_encode([
            "mensagem" => "Instrutor cadastrado com sucesso!",
            "id" => $pdo->lastInsertId()
        ]);
        exit;
    }

    http_response_code(404);
    echo json_encode([
        "erro" => "Rota não encontrada ou método HTTP incorreto."
    ]);

} catch (PDOException $e) {

    if ($e->getCode() === "23000") {
        http_response_code(409);
        echo json_encode([
            "erro" => "Este e-mail já está cadastrado ou existe um conflito nos dados."
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "erro" => "Erro ao acessar o banco de dados."
        ]);
    }
}

