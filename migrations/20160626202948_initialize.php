<?php

use Phinx\Migration\AbstractMigration;

class Initialize extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->execute('CREATE SEQUENCE message_log_id_seq INCREMENT BY 1 MINVALUE 1 START 1;');
        $this->execute('CREATE SEQUENCE message_id_seq INCREMENT BY 1 MINVALUE 1 START 1;');
        $this->execute('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1;');
        $this->execute('CREATE SEQUENCE event_id_seq INCREMENT BY 1 MINVALUE 1 START 1;');
        $this->execute('CREATE SEQUENCE application_id_seq INCREMENT BY 1 MINVALUE 1 START 1;');
        $this->execute('CREATE TABLE message_log (id INT NOT NULL, message_id INT DEFAULT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, state VARCHAR(50) NOT NULL, PRIMARY KEY(id));');
        $this->execute('CREATE INDEX IDX_A60AE229537A1329 ON message_log (message_id);');
        $this->execute('CREATE INDEX message_log_date ON message_log (date);');
        $this->execute('CREATE TABLE message (id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, title VARCHAR(255) NOT NULL, namespace VARCHAR(300) NOT NULL, payload TEXT NOT NULL, state VARCHAR(50) NOT NULL, partials TEXT DEFAULT NULL, PRIMARY KEY(id));');
        $this->execute('CREATE INDEX message_date ON message (date);');
        $this->execute('CREATE INDEX message_title ON message (title);');
        $this->execute('CREATE INDEX message_payload ON message (payload);');
        $this->execute('COMMENT ON COLUMN message.partials IS \'(DC2Type:simple_array)\';');
        $this->execute('CREATE TABLE users (id INT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, username VARCHAR(30) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id));');
        $this->execute('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username);');
        $this->execute('ALTER TABLE message_log ADD CONSTRAINT FK_A60AE229537A1329 FOREIGN KEY (message_id) REFERENCES message (id) NOT DEFERRABLE INITIALLY IMMEDIATE;');
        $this->execute('CREATE TABLE event (id INT NOT NULL, code VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(id));');
        $this->execute('CREATE UNIQUE INDEX UNIQ_3BAE0AA777153098 ON event (code);');
        $this->execute('CREATE TABLE event_application (event_id INT NOT NULL, application_id INT NOT NULL, PRIMARY KEY(event_id, application_id));');
        $this->execute('CREATE INDEX IDX_FD20E4171F7E88B ON event_application (event_id);');
        $this->execute('CREATE INDEX IDX_FD20E413E030ACD ON event_application (application_id);');
        $this->execute('CREATE TABLE application (id INT NOT NULL, code VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(512), events_type VARCHAR(20) NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(id));');
        $this->execute('CREATE UNIQUE INDEX UNIQ_A45BDDC177153098 ON application (code);');
        $this->execute('ALTER TABLE event_application ADD CONSTRAINT FK_FD20E4171F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;');
        $this->execute('ALTER TABLE event_application ADD CONSTRAINT FK_FD20E413E030ACD FOREIGN KEY (application_id) REFERENCES application (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;');
    }
}
