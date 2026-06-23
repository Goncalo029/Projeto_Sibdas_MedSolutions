====================================================================
 MedSolutions - Sistema de Gestão de Equipamentos Médicos Hospitalares
====================================================================


1. IDENTIFICAÇÃO

Nome do projeto : MedSolutions
Estudante       : Gonçalo Oliveira Pires
Número          : 1220673
Email           : 1220673@isep.ipp.pt


2. DESCRIÇÃO

Aplicação web para gestão de inventário de equipamentos médicos
num contexto hospitalar. Permite registar e acompanhar equipamentos,
localizações, fornecedores, documentos, garantias/contratos,
manutenções, empréstimos e movimentações, com dashboard de
indicadores, pesquisa avançada, histórico de alterações e gestão
de utilizadores.


3. TECNOLOGIAS

- PHP 8.3
- MySQL / MariaDB
- HTML5, CSS3, JavaScript
- Bibliotecas: Bootstrap 5, jQuery, DataTables, Flatpickr,
  FontAwesome, Chart.js
- Servidor de desenvolvimento: Laragon (Apache + PHP)


4. INSTALAÇÃO E EXECUÇÃO

4.1 - Requisitos
  - Laragon (ou XAMPP/WAMP) com PHP 8.3 e Apache
  - Extensões PHP: pdo_mysql e openssl (já vêm ativas no Laragon)
  - Ligação à rede do ISEP

4.2 - Colocar os ficheiros
  Copiar a pasta do projeto para dentro do "www" do Laragon,
  respeitando o caminho:

     C:\laragon\www\sibdas\1220673\MedSolutions

  Este caminho é importante porque corresponde ao BASE_URL
  definido em config/config.php.

4.3 - Base de dados
  A aplicação está ligada à base de dados remota do ISEP. Esta
  ligação já está configurada no ficheiro config/config.php e não
  é preciso alterar nada:

     Servidor   : vsgate-s1.dei.isep.ipp.pt
     Porta      : 10464
     Base       : db1220673
     Utilizador : 1220673


4.4 - Iniciar a aplicação
  a) Arrancar o Laragon (Start All).
  b) Abrir no navegador:

     http://127.0.0.1/sibdas/1220673/MedSolutions/public/

  Esta é a página pública (apresentação). O acesso ao painel
  faz-se pelo botão "Entrar na Plataforma" ou diretamente em:

     http://127.0.0.1/sibdas/1220673/MedSolutions/public/login.php


5. CREDENCIAIS DE ACESSO

A aplicação tem dois perfis. Na página de login existem ainda
botões de "Acesso Demo" que preenchem automaticamente estes dados.

  PERFIL: Administrador
    Email    : admin@hospital.pt
    Password : admin123

  PERFIL: Técnico
    Email    : tecnico@hospital.pt
    Password : tecnico123

Diferenças entre perfis:
  - O Administrador tem acesso completo, incluindo as áreas de
    Gestão, Mensagens de contacto, Utilizadores, Histórico de
    Alterações e edição do Website Público, bem como as ações
    sensíveis (apagar registos).
  - O Técnico tem acesso à operação do inventário (equipamentos,
    localizações, fornecedores, pesquisa) mas não às áreas de
    administração nem às ações de eliminação.


6. PRINCIPAIS TESTES DA APLICAÇÃO

6.1 - Autenticação
  - Tentar entrar com credenciais erradas -> deve mostrar erro.
  - Entrar como Administrador (ver credenciais acima).
  - Entrar como Técnico e confirmar que NÃO vê as áreas de
    administração (Utilizadores, Histórico, Website, Mensagens).
  - Fazer Logout.

6.2 - Dashboard
  - Após login, confirmar os cartões de resumo e os gráficos
    (estados, categorias, etc.).

6.3 - Equipamentos (CRUD principal)
  - Criar um equipamento novo (Inventário > Equipamentos > Novo).
    O código de inventário e a designação são obrigatórios.
  - Editar o equipamento criado.
  - Abrir os Detalhes e percorrer os separadores (documentos,
    manutenções, empréstimos, movimentações).
  - Exportar a lista para PDF.
  - Apagar o equipamento (apenas Administrador).

6.4 - Outros módulos
  - Criar/editar registos em Localizações, Fornecedores,
    Documentos e Garantias/Contrato.
  - Testar a Pesquisa avançada com vários filtros em simultâneo.

6.5 - Notificações
  - Confirmar o sino (topo direito) com os avisos (manutenções
    em atraso, garantias a expirar, etc.).

6.6 - Website público e contacto
  - Na página pública, submeter o formulário de contacto.
  - Entrar como Administrador e confirmar a mensagem em
    Comunicação > Mensagens.

6.7 - Histórico (apenas Administrador)
  - Confirmar que as ações de criar/editar/apagar ficam
    registadas no Histórico de Alterações.


7. INFORMAÇÃO ADICIONAL

- Estrutura de pastas:
    config/   -> configuração (ligação à BD e chaves)
    private/  -> área autenticada (views, includes, assets, uploads)
    public/   -> página pública, login, logout e assets públicos
    db1220673.sql -> cópia da base de dados (estrutura e dados)

- Segurança:
    . Passwords guardadas com hash (bcrypt).
    . Emails dos utilizadores encriptados na BD (AES-256).
    . Identificadores em alguns links encriptados (AES) para não
      expor IDs diretamente.
    . Acesso às páginas privadas protegido por sessão; ações
      sensíveis restritas ao perfil Administrador.

- Eliminação de registos: ao apagar um registo (equipamentos,
  localizações, categorias, fornecedores, documentos,
  garantias/contrato e utilizadores) este é removido
  definitivamente da base de dados. A única exceção são as
  mensagens de contacto, que ao serem apagadas ficam apenas
  ocultas (mantidas na base de dados).

- O ficheiro commits.txt (na raiz do projeto) contém o histórico
  de commits do desenvolvimento.
