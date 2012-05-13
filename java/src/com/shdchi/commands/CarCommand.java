package com.shdchi.commands;

public class CarCommand {

	enum CommandType {
		FORWARD, BACKWARD, WHEELS
	}

	public static final byte START = (byte) 0xD0;
	public static final byte STOP = (byte) 0XB0;

	private static final int COMMAND_TYPE_INDEX = 0;
	private static final int COMMAND_VALUE_INDEX = 1;

	public static byte[] getCommand(String stringCommandType, String value) {
		try {
			return getCommand(CommandType.valueOf(stringCommandType),  (byte)(Integer.parseInt(value) & 0xFF));
		} catch (NumberFormatException nfe) {
			return null;
		}
	}

	public static byte[] getCommand(CommandType type, byte value) {
		byte[] command = new byte[2];
		command[COMMAND_TYPE_INDEX] = commandTypeToByte(type);
		command[COMMAND_VALUE_INDEX] = value;
		return command;
	}

	private static byte commandTypeToByte(CommandType type) {
		switch (type) {
		case FORWARD:
			return (byte) 1;
		case BACKWARD:
			return (byte) 2;
		case WHEELS:
			return (byte) 3;
		default:
			return (byte) -1;
		}
	}

}
