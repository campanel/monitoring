#!/usr/bin/perl
#Author:cleber.motta@opservices.com.br)
#10-05-2013

use strict;
use Getopt::Long;
use POSIX;
use File::Basename;
use DBD::Oracle;
use Data::Dumper;
use Switch;

# Setting environment
$ENV{"ORACLE_HOME"}="/usr/lib/oracle/11.1/client";
$ENV{"LD_LIBRARY_PATH"}="/usr/X11R6/lib:/usr/lib:/lib:/usr/lib/oracle/11.1/client/lib:/usr/local/lib";

our $name = basename($0, ".pl");
our $path = "/usr/local/opmon/libexec";
our ($oHelp, $oWarn, $oCrit, $oTnsname, $oPort, $oUser, $oPass, $oDebug, $oMode);
$oDebug = 0;
#--------------------------------------------------------------------------------------
sub main {
        getoptions();
	my $chamados_totais_do_dia = " ". 
		"SELECT TRUNC(T.DIA) DATA, SUM(T.DENOMINADOR) QTD ".
		"  FROM BRM_WT_INDIC_TOT_CHAM_TB T, ".
		" BRM_WT_INDICADOR_TB I, ".
		" BRM_WT_DEPARTAMENTO_TB D ".
		" WHERE T.SKY_INDICADOR = I.SKY_INDICADOR ".
		" AND I.DSC_INDICADOR = 'QUANTIDADE DE CHAMADOS' ".
		" AND T.SKY_DEPARTAMENTO = D.SKY_DEPARTAMENTO ".
		" AND D.DSC_DEPARTAMENTO = 'DP' ".
		" AND D.SKY_DEPARTAMENTO = I.SKY_DEPARTAMENTO ".
		" AND T.NIVEL = 1 ".
		" AND I.FLG_ATIVO = 1 ".
		" AND D.FLG_ATIVO = 1 ".
		" AND TRUNC(T.DIA) = TRUNC(TO_DATE('02/05/2013','DD/MM/YYYY')) ".
#		" AND TRUNC(T.DIA) = TRUNC(SYSDATE) ".
		" GROUP BY T.DIA ".
		" ORDER BY 1 ";

	my $chamados_totais_do_dia_por_status = " ".
		" SELECT TRUNC(T.DIA) DATA, S.DSC_STATUS_CHAMADO STATUS, SUM(T.DENOMINADOR) QTD ".
		" FROM BRM_WT_INDIC_TOT_CHAM_TB T,  ".
		" BRM_WT_STATUS_CHAMADO_TB S, ".
		" BRM_WT_INDICADOR_TB I, ".
		" BRM_WT_DEPARTAMENTO_TB D ".
		" WHERE T.SKY_INDICADOR = I.SKY_INDICADOR ".
		" AND I.DSC_INDICADOR = 'QUANTIDADE DE CHAMADOS' ".
		" AND T.SKY_DEPARTAMENTO = D.SKY_DEPARTAMENTO ".
		" AND D.DSC_DEPARTAMENTO = 'DP' ".
		" AND D.SKY_DEPARTAMENTO = I.SKY_DEPARTAMENTO ".
		" AND T.SKY_STATUS_CHAMADO = S.SKY_STATUS_CHAMADO ".
		" AND T.NIVEL = 1 ".
		" AND S.FLG_ATIVO = 1 ".
		" AND I.FLG_ATIVO = 1 ".
		" AND D.FLG_ATIVO = 1 ".
                " AND TRUNC(T.DIA) = TRUNC(TO_DATE('02/05/2013','DD/MM/YYYY')) ".
		#" AND TRUNC(T.DIA) = TRUNC(SYSDATE) ".
		" GROUP BY S.DSC_STATUS_CHAMADO, T.DIA ".
		" ORDER BY 1, 2 ";

	my $acumulado_do_mes_por_status = " ".
		" SELECT S.DSC_STATUS_CHAMADO STATUS, SUM(T.DENOMINADOR) QTD ".
		" FROM BRM_WT_INDIC_TOT_CHAM_TB T, ".
		" BRM_WT_STATUS_CHAMADO_TB S, ".
		" BRM_WT_INDICADOR_TB I, ".
		" BRM_WT_DEPARTAMENTO_TB D ".
		" WHERE T.SKY_INDICADOR = I.SKY_INDICADOR ".
		" AND I.DSC_INDICADOR = 'QUANTIDADE DE CHAMADOS' ".
		" AND T.SKY_DEPARTAMENTO = D.SKY_DEPARTAMENTO ".
		" AND D.DSC_DEPARTAMENTO = 'DP' ".
		" AND D.SKY_DEPARTAMENTO = I.SKY_DEPARTAMENTO ".
		" AND T.SKY_STATUS_CHAMADO = S.SKY_STATUS_CHAMADO ".
		" AND T.NIVEL = 1 ".
		" AND S.FLG_ATIVO = 1 ".
		" AND I.FLG_ATIVO = 1 ".
		" AND D.FLG_ATIVO = 1 ".
		" AND TO_CHAR(T.DIA,'MM/YYYY') = TO_CHAR(SYSDATE,'MM/YYYY') ".
		" GROUP BY S.DSC_STATUS_CHAMADO ".
		" ORDER BY 1 ";
	my $query;
	
	switch ($oMode) {
		case 1 { $query = $chamados_totais_do_dia }
		case 2 { $query = $chamados_totais_do_dia_por_status }
		case 3 { $query = $acumulado_do_mes_por_status }
		else { help() }
	}
	
	
	my $hash = invokeQuery($query);
	print Dumper($hash);
	quit("fim...",0);

}

#--------------------------------------------------------------------------------------
sub invokeQuery {
        logger("Inicio query");
        my $query = shift;
 	print "$query \n" if($oDebug > 0);
        logger("Execute query: $query");
        my ($dbh, $sth);
        $dbh = DBI->connect("dbi:Oracle:$oTnsname", "$oUser", "$oPass");
	unless ($dbh) {
		print "Counld not connect $DBI::errstr\n";
		exit(2);
	}
        $sth = $dbh->prepare($query);
        $sth->execute();
	my $ref = $sth -> fetchall_arrayref([]);
	$sth->finish;
	return $ref;
}
#--------------------------------------------------------------------------------------
sub quit {
        my $mgs = shift;
        my $code = shift;
        print $mgs,"\n";
        exit($code);
}
#--------------------------------------------------------------------------------------
sub getoptions  {
        Getopt::Long::Configure('bundling');
        GetOptions(
                'h|help' => \$oHelp,
                'T|tnsname=s' => \$oTnsname,
                'p|port=i' => \$oPort,
                'U|username=s' => \$oUser,
                'P|password=s' => \$oPass,
		'm|mode=i' => \$oMode,
		'd|debug=i'    => \$oDebug,
        );
        if($oHelp){
                help();
        }
        if ((!$oUser) or (!$oPass)){
                help();
        }
}
#--------------------------------------------------------------------------------------
sub logger {
        if($oDebug > 0){
		my $msg = shift (@_);
		my $log = "$path/$name.log";
		my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst)=localtime(time);
		$wday++;
		$yday++;
		$mon++;
		$year+=1900;
		$isdst++;
		open(LOG, ">>$log");
		printf LOG ("%02i/%02i/%i - %02i:%02i:%02i => %s\n",$mday,$mon,$year,$hour,$min,$sec,$msg);
		close(LOG);
	}
}
#--------------------------------------------------------------------------------------
sub help {
       my $msg = "Usage: $name.pl [OPTION]...
-h, --help
-T, --tnsname
-p, --port
-U, --username
-P, --password
-m, --mode 
\t1 => CHAMADOS TOTAIS DO DIA
\t2 => CHAMADOS TOTAIS DO DIA POR STATUS
\t3 => ACUMULADO DO MÊS POR STATUS

Uso: ./$name.pl -T tnsname -U user -P senha -m numero";
	quit($msg,3);
}
#--------------------------------------------------------------------------------------

&main;
