<?php

use Phinx\Migration\AbstractMigration;

class OauthTables extends AbstractMigration
{
    public function change(): void
    {

        $this->table('clients')
            ->addColumn('client_id', 'string', ['length' => 32])
            ->addColumn('client_secret', 'string', ['length' => 64])
            ->addColumn('client_name', 'string', ['length' => 24])
            ->addColumn('confidential', 'boolean', ['default' => true])
            ->addColumn('grant_type', 'string', ['length' => 32])
            ->addTimestamps()
            ->addIndex('client_id', ['unique' => true])
            ->addIndex('client_secret', ['unique' => true])
            ->save();

        $this->table('client_redirect_domains')
            ->addColumn('client_id', 'integer')
            ->addColumn('domain', 'string')
            ->addTimestamps()
            ->addForeignKey('client_id', 'clients', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('scopes')
            ->addColumn('scope_name', 'string', ['length' => 32])
            ->addColumn('scope_description', 'text')
            ->addTimestamps()
            ->save();

        $this->table('access_tokens')
            ->addColumn('client_id', 'integer')
            ->addColumn('user_id', 'integer', ['null' => true, 'default' => null])
            ->addColumn('token', 'string')
            ->addColumn('is_revoked', 'boolean', ['default' => false])
            ->addColumn('expires', 'datetime')
            ->addTimestamps()
            ->addIndex('token', ['unique' => true])
            ->addIndex('is_revoked')
            ->addIndex('client_id')
            ->addForeignKey('client_id', 'clients', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('access_token_scopes')
            ->addColumn('token_id', 'integer')
            ->addColumn('scope_id', 'integer')
            ->addTimestamps()
            ->addIndex([ 'token_id', 'scope_id'], ['unique' => true ])
            ->addIndex('token_id')
            ->addForeignKey('token_id', 'access_tokens', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('scope_id', 'scopes', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('client_scopes')
            ->addColumn('client_id', 'integer')
            ->addColumn('scope_id', 'integer')
            ->addTimestamps()
            ->addIndex([ 'client_id', 'scope_id'], ['unique' => true ])
            ->addIndex('client_id')
            ->addForeignKey('client_id', 'clients', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('scope_id', 'scopes', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('users')
            ->addColumn('username', 'string')
            ->addColumn('email', 'string')
            ->addColumn('password', 'string')
            ->addIndex('username', ['unique' => true])
            ->addIndex('email', ['unique' => true])
            ->addTimestamps()
            ->create();

        $this->table('auth_codes')
            ->addColumn('user_id', 'integer')
            ->addColumn('client_id', 'integer')
            ->addColumn('code', 'string')
            ->addColumn('is_revoked', 'boolean', ['default' => false])
            ->addColumn('redirect_uri', 'string')
            ->addColumn('expires_at', 'datetime')
            ->addIndex('code', ['unique' => true])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('client_id', 'clients', 'id', ['delete' => 'CASCADE'])
            ->addTimestamps()
            ->create();

        $this->table('auth_code_scopes')
            ->addColumn('auth_code_id', 'integer')
            ->addColumn('scope_id', 'integer')
            ->addForeignKey('auth_code_id', 'auth_codes', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('scope_id', 'scopes', 'id', ['delete' => 'CASCADE'])
            ->addTimestamps()
            ->create();

        $this->table('refresh_tokens')
            ->addColumn('token', 'string')
            ->addColumn('access_token_id', 'integer')
            ->addColumn('is_revoked', 'boolean', ['default' => false])
            ->addColumn('expires_at', 'datetime')
            ->addForeignKey('access_token_id', 'access_tokens', 'id', ['delete' => 'CASCADE'])
            ->addTimestamps()
            ->create();
    }
}
