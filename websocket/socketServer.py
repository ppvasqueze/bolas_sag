"""
DESCRIPTION

	Clases que implementan el servidor socket que atiende las peticiones de
	la mascara QAD y del dashboard de la plaraforma.

EXAMPLES
	Cambios de estado del agente.
	Realizar llamada.
	Cortar llamada en curso.

EXIT STATUS

    Acusa recibo de la cadena a ser procesada.

AUTHOR

    CL3K LTDA <contacto@cl3k.com>

LICENSE
    This code is licensed under the terms of the 
    Contractual License Program (CLP) to Cinta Azul

VERSION

    $Id$
"""

import asyncore
import socket
import sys
import json
from datetime import datetime
from clslogger import logger

#class EchoHandler(asyncore.dispatcher_with_send):
class EchoHandler(asyncore.dispatcher):
	"""
	Clase encargada de procesar la informacion proveniente de la mascara QAD
	y del dashboard.
	"""
	astManInstance = ""
	dbInstance = ""
	wssrv=""
	def __init__(self, sock, IPAddress, chunk_size=256):
		"""
		Inicializacion de la clase
		"""
		self.logger = logger()
		self.chunk_size = chunk_size
		self.IPAddress = IPAddress
		asyncore.dispatcher.__init__(self, sock=sock)
		self.data_to_write = []
		return


	def handle_read(self):
		"""
		Metodo que procesa la data recibida
		"""
		data = self.recv(8192)
		if data:
			#self.logger.log('debug', 'RECIBIENDO COMANDO en (handle_read)', str(data), 1, '000', self.IPAddress)
			#arrData = data.splitlines()
			self.logger.log('debug', 'data recibida ', data, 1, '000', self.IPAddress)
			try:
				#self.wssrv.send_bytes(data)
				self.wssrv.sendMessage(data)
				"""
				jsonProceso = self.dataSegunTipoPeticion(str(arrData[0]))
				self.logger.log('debug', 'jsonProceso en (handle_read)', str(jsonProceso), 1, '000', self.IPAddress)
				dData = json.loads( jsonProceso )
				accion = dData['action']
				if self.commandoValido( accion ) == True:
					if accion == 'exit':
						sys.exit(1)
					elif accion == 'query':
						resp = config.qod.processQuery(dData)
						#self.logger.log('debug', 'RESPONDIENDO COMANDO en (handle_read)', str(resp), 1, '000', self.IPAddress)
						self.send(resp)
					else:
						processOrder(dData).start()
						result = '{"success":true, "response":"Data recibida", "datetime":"'+ str(datetime.now()) +'"}'
						self.send(result)
				else:
					result = '{"success":false, "response":"comando no reconocido.", "datetime":"'+ str(datetime.now()) +'"}'
					self.send(result)
				"""
			except Exception, e:
				result = '{"success":false, "response":"comando no reconocido."}'
				self.send(result)
				self.logger.log('error', 'ERROR AL RECIBIR DATA', str(e), 1, '000', self.IPAddress)

	def commandoValido (self, commando):
		"""
		Metodo que determina si el comando recibido es valido para ser
		procesado.
		"""
		try:
			posicion = config.listaComandos.index(commando)
			resultado = True
		except ValueError:
			resultado = False
		return resultado



	def dataSegunTipoPeticion(self, data):
		"""
		Metodo que determina si la data proviene de la mascara QAD (JSON)
		o del dashboard (HTML)
		"""
		primeraLinea = data.strip()
		EsApache = primeraLinea.find("GET")
		EsJson = primeraLinea.find("{")
		EsJsonFin = primeraLinea.rfind("}")
		
		if EsApache == 0:
			tipo = "Apache"
			comando = self.txtBtwStrs(primeraLinea, "/", "?")
			salida = '{"action":"query", "ip":"127.0.0.1", "query":"' + comando + '"}'
		elif EsJson == 0 and EsJsonFin > 0:
			tipo = "Json"
			salida = primeraLinea
		else:
			tipo = "Error"
			salida = '{"action":"error forzado"}'
		return salida


	def txtBtwStrs(self, s, leader, trailer):
		"""
		Metodo privado para obtener la data de un string contenida entre dos 
		caracteres
		"""
		end_of_leader = s.index(leader) + len(leader)
		start_of_trailer = s.index(trailer, end_of_leader)
		return s[end_of_leader:start_of_trailer]


	def attach(self, wssrv):
		self.wssrv = wssrv


class SocketServer(asyncore.dispatcher):
	"""
	Clase que implementa el servidor socket y que invoca la clase EchoHandler
	como un hilo independiente para el procesamiento de la informacion.
	"""
	astManInstance = ""
	dbInstance = ""
	handler=""
	wssrv=""
	def __init__(self, host, port):
		"""
		Inicializacion de la clase
		"""
		self.wssrv = ""
		self.logger = logger()
		asyncore.dispatcher.__init__(self)
		self.create_socket(socket.AF_INET, socket.SOCK_STREAM)
		self.set_reuse_addr()
		self.bind((host, port))
		self.listen(5)


	def handle_accept(self):
		"""
		Metodo invocado cuando el server recibe detos en el puerto.
		"""
		pair = self.accept()
		if pair is None:
			pass
		else:
			sock, addr = pair
			#self.logger.log('debug', 'RECIBIENDO DATA', repr(addr), 1, '000', str(addr[0]))
			self.handler = EchoHandler(sock, str(addr[0]))
			self.handler.attach(self.wssrv)

	def attach(self, wsServer):
		self.wssrv = wsServer
		
		