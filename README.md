# Empréstimos Ativos Audiovisuais

Plugin desenvolvido para WordPress com o objetivo de gerenciar o empréstimo de equipamentos e ativos audiovisuais, facilitando o controle de colaboradores, setores, movimentações e histórico de empréstimos.

## Sobre o projeto

O **Empréstimos Ativos Audiovisuais** é uma solução para organizar e centralizar o gerenciamento de equipamentos compartilhados em uma organização.

A ferramenta permite acompanhar os empréstimos, consultar a disponibilidade dos ativos e manter um histórico das movimentações, contribuindo para uma gestão mais eficiente do patrimônio.

## Funcionalidades

- **Dashboard:** painel administrativo para acompanhamento das informações.
- **Gestão de ativos:** cadastro e gerenciamento de equipamentos audiovisuais.
- **Gestão de colaboradores:** organização dos usuários responsáveis pelos empréstimos.
- **Gestão de setores:** cadastro e organização dos setores da empresa.
- **Controle de empréstimos:** registro e acompanhamento das retiradas e devoluções.
- **Histórico:** consulta das movimentações realizadas.
- **Catálogo:** visualização dos equipamentos disponíveis.
- **Painel do usuário:** acesso às informações relacionadas aos empréstimos.
- **Notificações:** suporte a notificações relacionadas às movimentações.
- **Integração com WhatsApp:** estrutura de integração para comunicação sobre os empréstimos.

## Tecnologias utilizadas

- PHP
- WordPress
- JavaScript
- CSS
- MySQL

## Estrutura do projeto

```text
emprestimos-plugin/
│
├── admin/
│   ├── views/
│   └── class-ea-admin.php
│
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
│
├── includes/
│   ├── class-ea-ajax.php
│   ├── class-ea-assets.php
│   ├── class-ea-database.php
│   ├── class-ea-notifications.php
│   ├── class-ea-shortcodes.php
│   └── class-ea-whatsapp.php
│
├── public/
│   └── class-ea-public.php
│
├── templates/
│
└── emprestimos-ativos.php
```

## Instalação

1. Faça o download ou clone este repositório.
2. Copie a pasta `emprestimos-plugin` para o diretório `wp-content/plugins/` da instalação WordPress.
3. Acesse o painel administrativo do WordPress.
4. Entre em **Plugins → Plugins instalados**.
5. Localize o plugin **Empréstimos Ativos Audiovisuais**.
6. Clique em **Ativar**.

## Objetivo

Desenvolver uma ferramenta para otimizar o controle de ativos audiovisuais, reduzir processos manuais e proporcionar maior organização e rastreabilidade dos empréstimos e devoluções.

## Autoria

Desenvolvido por **Beatriz Kailane**.

## Licença

Projeto desenvolvido para fins de gestão e controle de ativos audiovisuais.
