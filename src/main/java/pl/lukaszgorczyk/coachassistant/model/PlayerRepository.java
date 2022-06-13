package pl.lukaszgorczyk.coachassistant.model;

import java.util.List;
import java.util.Optional;

public interface PlayerRepository {
    List<Player> findAll();
    Optional<Player> findById(Integer id);
    Player save(Player entity);
}
