DELETE FROM madmakers_obooking.clients WHERE  site_id=67;
DELETE FROM madmakers_obooking.client_statuses WHERE site_id=67;
DELETE FROM madmakers_obooking.clients_balance_history WHERE site_id=67;
DELETE FROM madmakers_obooking.records WHERE site_id=67;
DELETE FROM madmakers_obooking.records_clients WHERE site_id=67;

DELETE FROM madmakers_obooking.card_types WHERE site_id=67;
DELETE FROM madmakers_obooking.clients_cards WHERE site_id=67;
DELETE FROM madmakers_obooking.subscription_types WHERE site_id=67;
DELETE FROM madmakers_obooking.clients_subscriptions WHERE site_id=67;

USE madmakers_obooking;
create table tmp_talanto_abonements
(
	id int auto_increment,
	talanto_uuid varchar(255) null,
	duration_days int default 0 not null,
	start_date varchar(255) null,
	end_date varchar(255) null,
	price int default 0 not null,
	visits_amount int default 0 not null,
	visits_left int default 0 not null,
	Office varchar(255) null,
	manager varchar(255) null,
	admin varchar(255) null,
	client varchar(255) null,
	abonement_title varchar(255) null,
	site_id int not null,
	constraint tmp_talanto_abonements_pk
		primary key (id)
);

create index tmp_talanto_abonements_site_id_index
	on tmp_talanto_abonements (site_id);

create index tmp_talanto_abonements_talanto_uuid_index
	on tmp_talanto_abonements (talanto_uuid);


alter table madmakers_obooking.clients
	add tmp_talanto_card_no varchar(255) null;

alter table madmakers_obooking.clients
	add tmp_talanto_office varchar(255) null;

alter table madmakers_obooking.clients
	add tmp_talanto_client_type varchar(255) null;

alter table madmakers_obooking.clients
	add tmp_talanto_course_1 varchar(255) null;

alter table madmakers_obooking.clients
	add tmp_talanto_course_2 varchar(255) null;

alter table madmakers_obooking.clients
	add tmp_talanto_course_3 varchar(255) null;

alter table madmakers_obooking.clients
	add tmp_talanto_manager varchar(255) null;

alter table madmakers_obooking.clients
	add tmp_talanto_has_card varchar(255) null;




/*AFTER SQL SCRIPTS*/

UPDATE madmakers_obooking.clients SET tmp_talanto_has_card=0 where tmp_talanto_has_card='ЛОЖЬ'
UPDATE madmakers_obooking.clients SET tmp_talanto_has_card=1 where tmp_talanto_has_card='ИСТИНА'
