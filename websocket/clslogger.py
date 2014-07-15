#!/usr/bin/env python2.6
"""
DESCRIPTION

	Clase encargada de dar un formato a la data que se envia al archivo de log.

EXAMPLES
	[2013-04-15 11:42:24.300791] [debug] [192.168.0.40] [000] 
	metodo:(validarAnexo linea:181) -- [cmdSetAgentStatus] 
	[DATOS DE LA BASE DE AGENTES 0] --> 192.168.0.40

EXIT STATUS

    Archivo log con formato estandarizado.

AUTHOR

    CL3K LTDA <contacto@cl3k.com>

LICENSE
    This code is licensed under the terms of the 
    Contractual License Program (CLP) to Cinta Azul

VERSION

    $Id$
"""

import sys
import os
import time
import inspect
from datetime import datetime
import traceback
import pprint

class logger:
	"""
	Clase encargada de escribir en el archivo de log en forma estandar
	"""
	logactivities = 1

	def __init__(self):
		"""
		Inicializacion de la clase
		"""
		pass
		
		
	def log(self, type, title, msg, showLog=None, anexo='000', ip='0.0.0.0'):
		"""
		Metodo que implementa la escritura en el archivo de log.
		"""
		if self.logactivities > 0:
			show = 0
			if showLog is None:
				show = 0
			else:
				if showLog == 1:
					show = 1
				else:	
					show = 0
				
			if type.lower() == "error":
				show = 1
	
			if show == 1:
				try:
					sys.stderr.write("\n[" + str(datetime.now()) + "] [" + str(type) + "] [" + str(ip) + "] [" + str(anexo) + "] metodo:(" + str(inspect.stack()[1][3]) + " linea:"+ str(inspect.currentframe().f_back.f_lineno) + ") -- " + str(title) + " --> " + str(msg) + "\n")
					if type.lower() == "error":
						traceback.print_exc()
				except Exception, e:
					sys.stderr.write("\n[" + str(datetime.now()) + "] [" + str(type) + "] [" + str(ip) + "] [" + str(anexo) + "] ERROR EN LOG PARAMS() --> " + str(e))
					traceback.print_exc()