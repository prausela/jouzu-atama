CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `set` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `categoryId` int NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`categoryId`) REFERENCES `category`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT ucSetNamePerCategory UNIQUE (`name`, `categoryId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `question` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `setId` int NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`setId`) REFERENCES `set`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `answer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `questionId` int NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`questionId`) REFERENCES `question`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT ucAnswersPerQuestion UNIQUE (`name`, `questionId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `question`
ADD `correctAnswer` int NOT NULL;

ALTER TABLE `question`
ADD FOREIGN KEY (`correctAnswer`) REFERENCES `answer`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;